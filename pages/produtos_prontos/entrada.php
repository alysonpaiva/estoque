<?php
/**
 * Página de Entrada de Produtos Prontos
 * Sistema de Controle de Estoque - Pizzaria
 */

require_once '../../config/config.php';

$erro = '';
$sucesso = '';
$entrada = null;

// Verificar se é edição
$editando = false;
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $editando = true;
    $entrada = EntradaDireta::buscarPorId($_GET['id']);
    if (!$entrada) {
        header('Location: listar_entradas.php?erro=Entrada não encontrada');
        exit;
    }
}

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $produtoId = (int)($_POST['produto_id'] ?? 0);
        $quantidadeEntrada = (float)($_POST['quantidade_entrada'] ?? 0);
        $precoUnitario = (float)($_POST['preco_unitario'] ?? 0);
        $fornecedor = sanitizeInput($_POST['fornecedor'] ?? '');
        $notaFiscal = sanitizeInput($_POST['nota_fiscal'] ?? '');
        $observacoes = sanitizeInput($_POST['observacoes'] ?? '');
        
        if ($editando && $entrada) {
            // Atualizar entrada existente
            $entrada->setProdutoId($produtoId);
            $entrada->setQuantidadeEntrada($quantidadeEntrada);
            $entrada->setPrecoUnitario($precoUnitario);
            $entrada->setFornecedor($fornecedor);
            $entrada->setNotaFiscal($notaFiscal);
            $entrada->setObservacoes($observacoes);
        } else {
            // Criar nova entrada
            $entrada = new EntradaDireta($produtoId, $quantidadeEntrada, $precoUnitario);
            $entrada->setFornecedor($fornecedor);
            $entrada->setNotaFiscal($notaFiscal);
            $entrada->setObservacoes($observacoes);
        }
        
        // Salvar entrada
        if ($entrada->salvar()) {
            $sucesso = $editando ? 'Entrada atualizada com sucesso!' : 'Entrada registrada com sucesso!';
            if (!$editando) {
                // Limpar formulário após inserção
                $entrada = null;
            }
        } else {
            $erro = 'Erro ao salvar entrada. Tente novamente.';
        }
    } catch (Exception $e) {
        $erro = 'Erro: ' . $e->getMessage();
    }
}

// Buscar produtos prontos
$produtosProntos = [];
try {
    $db = Database::getInstance();
    $sql = "SELECT id, nome, unidade_medida FROM produtos WHERE tipo_produto = 'produto_pronto' AND ativo = 1 ORDER BY nome";
    $produtosProntos = $db->fetchAll($sql);
} catch (Exception $e) {
    $erro = 'Erro ao carregar produtos: ' . $e->getMessage();
}

$pageTitle = ($editando ? 'Editar' : 'Nova') . ' Entrada de Produto Pronto - ' . APP_NAME;
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
                            <li><a class="dropdown-item active" href="entrada.php">Nova Entrada</a></li>
                            <li><a class="dropdown-item" href="listar_entradas.php">Listar Entradas</a></li>
                            <li><a class="dropdown-item" href="retirada.php">Nova Retirada</a></li>
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
                <li class="breadcrumb-item"><a href="listar_entradas.php">Produtos Prontos</a></li>
                <li class="breadcrumb-item active"><?= $editando ? 'Editar' : 'Nova' ?> Entrada</li>
            </ol>
        </nav>

        <!-- Título -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3">
                <i class="bi bi-plus-circle"></i>
                <?= $editando ? 'Editar' : 'Nova' ?> Entrada de Produto Pronto
            </h1>
            <a href="listar_entradas.php" class="btn btn-outline-secondary">
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
                            Dados da Entrada
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label for="produto_id" class="form-label">
                                    Produto <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="produto_id" name="produto_id" required>
                                    <option value="">Selecione o produto...</option>
                                    <?php foreach ($produtosProntos as $produto): ?>
                                        <?php 
                                        $selected = ($entrada && $entrada->getProdutoId() == $produto['id']) ? 'selected' : '';
                                        ?>
                                        <option value="<?= $produto['id'] ?>" <?= $selected ?>>
                                            <?= htmlspecialchars($produto['nome']) ?> (<?= $produto['unidade_medida'] ?>)
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
                                        <label for="quantidade_entrada" class="form-label">
                                            Quantidade <span class="text-danger">*</span>
                                        </label>
                                        <input type="number" 
                                               class="form-control" 
                                               id="quantidade_entrada" 
                                               name="quantidade_entrada"
                                               step="0.001"
                                               min="0.001"
                                               value="<?= $entrada ? number_format($entrada->getQuantidadeEntrada(), 3, '.', '') : '' ?>"
                                               required>
                                        <div class="invalid-feedback">
                                            Por favor, informe a quantidade.
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="preco_unitario" class="form-label">
                                            Preço Unitário <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text">R$</span>
                                            <input type="number" 
                                                   class="form-control" 
                                                   id="preco_unitario" 
                                                   name="preco_unitario"
                                                   step="0.01"
                                                   min="0.01"
                                                   value="<?= $entrada ? number_format($entrada->getPrecoUnitario(), 2, '.', '') : '' ?>"
                                                   required>
                                        </div>
                                        <div class="invalid-feedback">
                                            Por favor, informe o preço unitário.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="fornecedor" class="form-label">
                                            Fornecedor
                                        </label>
                                        <input type="text" 
                                               class="form-control" 
                                               id="fornecedor" 
                                               name="fornecedor"
                                               maxlength="100"
                                               value="<?= $entrada ? htmlspecialchars($entrada->getFornecedor()) : '' ?>"
                                               placeholder="Nome do fornecedor">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="nota_fiscal" class="form-label">
                                            Nota Fiscal
                                        </label>
                                        <input type="text" 
                                               class="form-control" 
                                               id="nota_fiscal" 
                                               name="nota_fiscal"
                                               maxlength="50"
                                               value="<?= $entrada ? htmlspecialchars($entrada->getNotaFiscal()) : '' ?>"
                                               placeholder="Número da nota fiscal">
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
                                          placeholder="Observações sobre a entrada..."><?= $entrada ? htmlspecialchars($entrada->getObservacoes()) : '' ?></textarea>
                                <div class="form-text">
                                    Máximo 500 caracteres
                                </div>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="listar_entradas.php" class="btn btn-secondary me-md-2">
                                    <i class="bi bi-x-circle"></i> Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle"></i>
                                    <?= $editando ? 'Atualizar' : 'Registrar' ?> Entrada
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
                            <h6><i class="bi bi-lightbulb"></i> Produtos Prontos</h6>
                            <p class="mb-0">
                                São produtos que já vêm prontos para uso, não necessitando de produção.
                                Exemplos: chocolate bisnaga, refrigerantes, molhos prontos.
                            </p>
                        </div>

                        <div class="alert alert-warning">
                            <h6><i class="bi bi-exclamation-triangle"></i> Atenção</h6>
                            <p class="mb-0">
                                Certifique-se de que o produto está cadastrado como "Produto Pronto" 
                                antes de registrar a entrada.
                            </p>
                        </div>

                        <div id="valor_total_preview" class="alert alert-success" style="display: none;">
                            <h6><i class="bi bi-calculator"></i> Valor Total</h6>
                            <p class="mb-0">
                                <strong id="valor_calculado">R$ 0,00</strong>
                            </p>
                        </div>
                    </div>
                </div>
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

        // Calcular valor total
        function calcularValorTotal() {
            const quantidade = parseFloat(document.getElementById('quantidade_entrada').value) || 0;
            const precoUnitario = parseFloat(document.getElementById('preco_unitario').value) || 0;
            const valorTotal = quantidade * precoUnitario;
            
            if (valorTotal > 0) {
                document.getElementById('valor_calculado').textContent = 
                    'R$ ' + valorTotal.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                document.getElementById('valor_total_preview').style.display = 'block';
            } else {
                document.getElementById('valor_total_preview').style.display = 'none';
            }
        }

        // Event listeners para cálculo automático
        document.getElementById('quantidade_entrada').addEventListener('input', calcularValorTotal);
        document.getElementById('preco_unitario').addEventListener('input', calcularValorTotal);

        // Calcular valor inicial se estiver editando
        document.addEventListener('DOMContentLoaded', function() {
            calcularValorTotal();
        });
    </script>
</body>
</html>

