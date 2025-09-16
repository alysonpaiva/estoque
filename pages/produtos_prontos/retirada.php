<?php
/**
 * Página de Retirada de Produtos Prontos
 * Sistema de Controle de Estoque - Pizzaria
 */

require_once '../../config/config.php';

$erro = '';
$sucesso = '';
$retirada = null;

// Verificar se é edição
$editando = false;
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $editando = true;
    $retirada = RetiradaDireta::buscarPorId($_GET['id']);
    if (!$retirada) {
        header('Location: listar_retiradas.php?erro=Retirada não encontrada');
        exit;
    }
}

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $produtoId = (int)($_POST['produto_id'] ?? 0);
        $quantidadeRetirada = (float)($_POST['quantidade_retirada'] ?? 0);
        $destino = sanitizeInput($_POST['destino'] ?? '');
        $observacoes = sanitizeInput($_POST['observacoes'] ?? '');
        
        if ($editando && $retirada) {
            // Atualizar retirada existente
            $retirada->setProdutoId($produtoId);
            $retirada->setQuantidadeRetirada($quantidadeRetirada);
            $retirada->setDestino($destino);
            $retirada->setObservacoes($observacoes);
        } else {
            // Criar nova retirada
            $retirada = new RetiradaDireta($produtoId, $quantidadeRetirada);
            $retirada->setDestino($destino);
            $retirada->setObservacoes($observacoes);
        }
        
        // Salvar retirada
        if ($retirada->salvar()) {
            $sucesso = $editando ? 'Retirada atualizada com sucesso!' : 'Retirada registrada com sucesso!';
            if (!$editando) {
                // Limpar formulário após inserção
                $retirada = null;
            }
        } else {
            $erro = 'Erro ao salvar retirada. Tente novamente.';
        }
    } catch (Exception $e) {
        $erro = 'Erro: ' . $e->getMessage();
    }
}

// Buscar produtos prontos com estoque
$produtosDisponiveis = [];
try {
    $db = Database::getInstance();
    $sql = "SELECT p.id, p.nome, p.unidade_medida,
                   COALESCE(SUM(ed.quantidade_entrada), 0) - COALESCE(SUM(rd.quantidade_retirada), 0) as estoque_atual
            FROM produtos p
            LEFT JOIN entradas_diretas ed ON p.id = ed.produto_id
            LEFT JOIN retiradas_diretas rd ON p.id = rd.produto_id
            WHERE p.tipo_produto = 'produto_pronto' AND p.ativo = 1
            GROUP BY p.id, p.nome, p.unidade_medida
            HAVING estoque_atual > 0
            ORDER BY p.nome";
    $produtosDisponiveis = $db->fetchAll($sql);
} catch (Exception $e) {
    $erro = 'Erro ao carregar produtos: ' . $e->getMessage();
}

$pageTitle = ($editando ? 'Editar' : 'Nova') . ' Retirada de Produto Pronto - ' . APP_NAME;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="../../pages/relatorios/dashboard.php">
                <i class="bi bi-boxes"></i> Sistema de Controle de Estoque
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../../pages/relatorios/dashboard.php">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-box-seam"></i> Produtos
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="../produtos/cadastrar.php">Cadastrar Produto</a></li>
                            <li><a class="dropdown-item" href="../produtos/listar.php">Listar Produtos</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown active">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-bag-check"></i> Produtos Prontos
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="entrada.php">Nova Entrada</a></li>
                            <li><a class="dropdown-item" href="listar_entradas.php">Listar Entradas</a></li>
                            <li><a class="dropdown-item active" href="retirada.php">Nova Retirada</a></li>
                            <li><a class="dropdown-item" href="listar_retiradas.php">Listar Retiradas</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../../pages/relatorios/dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="listar_retiradas.php">Produtos Prontos</a></li>
                <li class="breadcrumb-item active"><?= $editando ? 'Editar' : 'Nova' ?> Retirada</li>
            </ol>
        </nav>

        <!-- Título -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3">
                <i class="bi bi-box-arrow-right"></i>
                <?= $editando ? 'Editar' : 'Nova' ?> Retirada de Produto Pronto
            </h1>
            <a href="listar_retiradas.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>

        <!-- Alertas -->
        <?php if ($erro): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle"></i>
                <?= $erro ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($sucesso): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle"></i>
                <?= $sucesso ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Formulário -->
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-clipboard-data"></i>
                            Dados da Retirada
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label for="produto_id" class="form-label">
                                    Produto <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="produto_id" name="produto_id" required onchange="atualizarEstoque()">
                                    <option value="">Selecione o produto...</option>
                                    <?php foreach ($produtosDisponiveis as $produto): ?>
                                        <?php 
                                        $selected = ($retirada && $retirada->getProdutoId() == $produto['id']) ? 'selected' : '';
                                        ?>
                                        <option value="<?= $produto['id'] ?>" 
                                                data-estoque="<?= $produto['estoque_atual'] ?>"
                                                data-unidade="<?= $produto['unidade_medida'] ?>"
                                                <?= $selected ?>>
                                            <?= htmlspecialchars($produto['nome']) ?> 
                                            (Estoque: <?= number_format($produto['estoque_atual'], 3, ',', '.') ?> <?= $produto['unidade_medida'] ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">
                                    Por favor, selecione um produto.
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="quantidade_retirada" class="form-label">
                                            Quantidade <span class="text-danger">*</span>
                                        </label>
                                        <input type="number" 
                                               class="form-control" 
                                               id="quantidade_retirada" 
                                               name="quantidade_retirada"
                                               step="0.001"
                                               min="0.001"
                                               value="<?= $retirada ? number_format($retirada->getQuantidadeRetirada(), 3, '.', '') : '' ?>"
                                               required>
                                        <div class="invalid-feedback">
                                            Por favor, informe a quantidade.
                                        </div>
                                        <div id="estoque_info" class="form-text"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="destino" class="form-label">
                                            Destino <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-select" id="destino" name="destino" required>
                                            <option value="">Selecione o destino...</option>
                                            <?php
                                            $destinos = [
                                                'pizzaria' => 'Pizzaria',
                                                'delivery' => 'Delivery',
                                                'consumo_interno' => 'Consumo Interno',
                                                'perda' => 'Perda/Descarte',
                                                'outro' => 'Outro'
                                            ];
                                            $destinoAtual = $retirada ? $retirada->getDestino() : '';
                                            foreach ($destinos as $valor => $label) {
                                                $selected = ($destinoAtual === $valor) ? 'selected' : '';
                                                echo "<option value=\"$valor\" $selected>$label</option>";
                                            }
                                            ?>
                                        </select>
                                        <div class="invalid-feedback">
                                            Por favor, selecione o destino.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="observacoes" class="form-label">
                                    Observações
                                </label>
                                <textarea class="form-control" 
                                          id="observacoes" 
                                          name="observacoes" 
                                          rows="3"
                                          maxlength="500"
                                          placeholder="Observações sobre a retirada..."><?= $retirada ? htmlspecialchars($retirada->getObservacoes()) : '' ?></textarea>
                                <div class="form-text">
                                    Máximo 500 caracteres
                                </div>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="listar_retiradas.php" class="btn btn-secondary me-md-2">
                                    <i class="bi bi-x-circle"></i> Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle"></i>
                                    <?= $editando ? 'Atualizar' : 'Registrar' ?> Retirada
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Sidebar com informações -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="bi bi-info-circle"></i>
                            Informações
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <h6><i class="bi bi-lightbulb"></i> Retirada de Produtos Prontos</h6>
                            <p class="mb-0">
                                Registre aqui as saídas de produtos prontos do estoque principal 
                                para a pizzaria, delivery ou outros destinos.
                            </p>
                        </div>

                        <div class="alert alert-warning">
                            <h6><i class="bi bi-exclamation-triangle"></i> Atenção</h6>
                            <p class="mb-0">
                                Só é possível retirar produtos que tenham estoque disponível. 
                                Verifique o estoque atual antes de registrar a retirada.
                            </p>
                        </div>

                        <?php if (empty($produtosDisponiveis)): ?>
                            <div class="alert alert-danger">
                                <h6><i class="bi bi-exclamation-circle"></i> Sem Estoque</h6>
                                <p class="mb-0">
                                    Não há produtos prontos com estoque disponível para retirada.
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Produtos com estoque -->
                <?php if (!empty($produtosDisponiveis)): ?>
                    <div class="card mt-3">
                        <div class="card-header">
                            <h6 class="card-title mb-0">
                                <i class="bi bi-boxes"></i>
                                Produtos Disponíveis
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="list-group list-group-flush">
                                <?php foreach (array_slice($produtosDisponiveis, 0, 5) as $produto): ?>
                                    <div class="list-group-item px-0">
                                        <div class="d-flex justify-content-between">
                                            <small class="fw-bold"><?= htmlspecialchars($produto['nome']) ?></small>
                                            <small class="text-success">
                                                <?= number_format($produto['estoque_atual'], 3, ',', '.') ?> <?= $produto['unidade_medida'] ?>
                                            </small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                <?php if (count($produtosDisponiveis) > 5): ?>
                                    <div class="list-group-item px-0 text-center">
                                        <small class="text-muted">
                                            E mais <?= count($produtosDisponiveis) - 5 ?> produtos...
                                        </small>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Validação do formulário
        (function() {
            'use strict';
            window.addEventListener('load', function() {
                var forms = document.getElementsByClassName('needs-validation');
                var validation = Array.prototype.filter.call(forms, function(form) {
                    form.addEventListener('submit', function(event) {
                        if (form.checkValidity() === false) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                        form.classList.add('was-validated');
                    }, false);
                });
            }, false);
        })();

        // Atualizar informações de estoque
        function atualizarEstoque() {
            const select = document.getElementById('produto_id');
            const quantidadeInput = document.getElementById('quantidade_retirada');
            const estoqueInfo = document.getElementById('estoque_info');
            
            if (select.value) {
                const option = select.options[select.selectedIndex];
                const estoque = parseFloat(option.dataset.estoque);
                const unidade = option.dataset.unidade;
                
                estoqueInfo.innerHTML = `<i class="bi bi-info-circle"></i> Estoque disponível: <strong>${estoque.toLocaleString('pt-BR', {minimumFractionDigits: 3})} ${unidade}</strong>`;
                estoqueInfo.className = 'form-text text-info';
                
                // Definir máximo para o input
                quantidadeInput.max = estoque;
                
                // Validar quantidade em tempo real
                quantidadeInput.addEventListener('input', function() {
                    const quantidade = parseFloat(this.value) || 0;
                    if (quantidade > estoque) {
                        estoqueInfo.innerHTML = `<i class="bi bi-exclamation-triangle"></i> Quantidade maior que o estoque disponível!`;
                        estoqueInfo.className = 'form-text text-danger';
                        this.setCustomValidity('Quantidade maior que o estoque disponível');
                    } else {
                        estoqueInfo.innerHTML = `<i class="bi bi-info-circle"></i> Estoque disponível: <strong>${estoque.toLocaleString('pt-BR', {minimumFractionDigits: 3})} ${unidade}</strong>`;
                        estoqueInfo.className = 'form-text text-info';
                        this.setCustomValidity('');
                    }
                });
            } else {
                estoqueInfo.innerHTML = '';
                quantidadeInput.removeAttribute('max');
            }
        }

        // Atualizar estoque inicial se estiver editando
        document.addEventListener('DOMContentLoaded', function() {
            atualizarEstoque();
        });
    </script>
</body>
</html>

