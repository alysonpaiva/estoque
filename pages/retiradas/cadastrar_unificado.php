<?php
/**
 * Página Unificada de Cadastro de Retiradas
 * Sistema de Controle de Estoque - Pizzaria
 * Suporta tanto produtos de produção quanto produtos prontos
 */

require_once '../../config/config.php';

$erro = '';
$sucesso = '';
$retirada = null;

// Verificar se é edição
$editando = false;
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $editando = true;
    $retirada = Retirada::buscarPorId($_GET['id']);
    if (!$retirada) {
        header('Location: listar.php?erro=Retirada não encontrada');
        exit;
    }
}

// Carregar opções disponíveis
try {
    // Produções disponíveis (produtos que passaram por produção)
    $producoes = Producao::listarDisponiveis();
    
    // Produtos prontos com estoque (entradas diretas)
    $db = Database::getInstance();
    $produtosProntos = $db->fetchAll("
        SELECT p.id, p.nome, p.unidade_medida,
               COALESCE(SUM(ed.quantidade_entrada), 0) - COALESCE(SUM(rd.quantidade_retirada), 0) as estoque_disponivel
        FROM produtos p
        LEFT JOIN entradas_diretas ed ON p.id = ed.produto_id
        LEFT JOIN retiradas_diretas rd ON p.id = rd.produto_id
        WHERE p.tipo_produto = 'produto_pronto' AND p.ativo = 1
        GROUP BY p.id, p.nome, p.unidade_medida
        HAVING estoque_disponivel > 0
        ORDER BY p.nome
    ");
} catch (Exception $e) {
    $erro = 'Erro ao carregar opções: ' . $e->getMessage();
    $producoes = [];
    $produtosProntos = [];
}

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $tipoRetirada = sanitizeInput($_POST['tipo_retirada'] ?? '');
        $itemId = (int) ($_POST['item_id'] ?? 0);
        $quantidadeRetirada = (float) ($_POST['quantidade_retirada'] ?? 0);
        $destino = sanitizeInput($_POST['destino'] ?? '');
        $responsavel = sanitizeInput($_POST['responsavel'] ?? '');
        $observacoes = sanitizeInput($_POST['observacoes'] ?? '');
        
        // Validações
        if (empty($tipoRetirada) || !in_array($tipoRetirada, ['producao', 'produto_pronto'])) {
            throw new Exception('Tipo de retirada inválido.');
        }
        
        if ($itemId <= 0) {
            throw new Exception('Selecione um item válido.');
        }
        
        if ($quantidadeRetirada <= 0) {
            throw new Exception('Quantidade deve ser maior que zero.');
        }
        
        if (empty($destino)) {
            throw new Exception('Destino é obrigatório.');
        }
        
        if ($tipoRetirada === 'producao') {
            // Retirada de produção (sistema atual)
            if ($editando && $retirada) {
                $retirada->setProducaoId($itemId);
                $retirada->setQuantidadeRetirada($quantidadeRetirada);
                $retirada->setDestino($destino);
                $retirada->setResponsavel($responsavel);
                $retirada->setObservacoes($observacoes);
            } else {
                $retirada = new Retirada($itemId, $quantidadeRetirada, $destino);
                if ($responsavel) $retirada->setResponsavel($responsavel);
                if ($observacoes) $retirada->setObservacoes($observacoes);
            }
        } else {
            // Retirada de produto pronto (novo)
            if ($editando && $retirada) {
                // Para edição, usar RetiradaDireta se existir
                $retiradaDireta = new RetiradaDireta($itemId, $quantidadeRetirada, $destino);
                if ($responsavel) $retiradaDireta->setResponsavel($responsavel);
                if ($observacoes) $retiradaDireta->setObservacoes($observacoes);
                $retiradaDireta->setId($retirada->getId());
                $retirada = $retiradaDireta;
            } else {
                $retirada = new RetiradaDireta($itemId, $quantidadeRetirada, $destino);
                if ($responsavel) $retirada->setResponsavel($responsavel);
                if ($observacoes) $retirada->setObservacoes($observacoes);
            }
        }
        
        if ($retirada->salvar()) {
            $mensagem = $editando ? 'Retirada atualizada com sucesso!' : 'Retirada registrada com sucesso!';
            header("Location: listar.php?sucesso=" . urlencode($mensagem));
            exit;
        } else {
            $erro = 'Erro ao salvar retirada. Tente novamente.';
        }
    } catch (Exception $e) {
        $erro = $e->getMessage();
    }
}

$pageTitle = ($editando ? 'Editar' : 'Nova') . ' Retirada - ' . APP_NAME;
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
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-archive"></i> Lotes
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="../lotes/cadastrar.php">Novo Lote</a></li>
                            <li><a class="dropdown-item" href="../lotes/listar.php">Listar Lotes</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-gear"></i> Produção
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="../producao/cadastrar.php">Nova Produção</a></li>
                            <li><a class="dropdown-item" href="../producao/listar.php">Listar Produções</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown active">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-truck"></i> Retiradas
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item active" href="cadastrar_unificado.php">Nova Retirada</a></li>
                            <li><a class="dropdown-item" href="listar.php">Listar Retiradas</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-currency-dollar"></i> Vendas
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="../vendas/cadastrar.php">Nova Venda</a></li>
                            <li><a class="dropdown-item" href="../vendas/listar.php">Listar Vendas</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-graph-up"></i> Relatórios
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="../relatorios/dashboard.php">Dashboard</a></li>
                            <li><a class="dropdown-item" href="../relatorios/entradas.php">Entradas</a></li>
                            <li><a class="dropdown-item" href="../relatorios/producao.php">Produção</a></li>
                            <li><a class="dropdown-item" href="../relatorios/retiradas.php">Retiradas</a></li>
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
                <li class="breadcrumb-item"><a href="listar.php">Retiradas</a></li>
                <li class="breadcrumb-item active"><?= $editando ? 'Editar' : 'Nova' ?> Retirada</li>
            </ol>
        </nav>

        <!-- Título -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3">
                <i class="bi bi-truck"></i>
                <?= $editando ? 'Editar' : 'Nova' ?> Retirada
            </h1>
            <a href="listar.php" class="btn btn-outline-secondary">
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

        <!-- Formulário -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-clipboard-data"></i>
                    Dados da Retirada
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" id="formRetirada">
                    <div class="row">
                        <!-- Tipo de Retirada -->
                        <div class="col-md-6 mb-3">
                            <label for="tipo_retirada" class="form-label">
                                <i class="bi bi-tags"></i> Tipo de Retirada *
                            </label>
                            <select class="form-select" id="tipo_retirada" name="tipo_retirada" required onchange="atualizarItens()">
                                <option value="">Selecione o tipo...</option>
                                <option value="producao" <?= (!$editando || ($retirada && get_class($retirada) === 'Retirada')) ? 'selected' : '' ?>>
                                    Produto de Produção
                                </option>
                                <option value="produto_pronto" <?= ($retirada && get_class($retirada) === 'RetiradaDireta') ? 'selected' : '' ?>>
                                    Produto Pronto
                                </option>
                            </select>
                            <div class="form-text">
                                Produto de Produção: itens que passaram pelo processo de produção<br>
                                Produto Pronto: itens que não precisam de produção (ex: refrigerantes, chocolates)
                            </div>
                        </div>

                        <!-- Item -->
                        <div class="col-md-6 mb-3">
                            <label for="item_id" class="form-label">
                                <i class="bi bi-box"></i> Item *
                            </label>
                            <select class="form-select" id="item_id" name="item_id" required>
                                <option value="">Primeiro selecione o tipo...</option>
                            </select>
                        </div>

                        <!-- Quantidade -->
                        <div class="col-md-4 mb-3">
                            <label for="quantidade_retirada" class="form-label">
                                <i class="bi bi-123"></i> Quantidade *
                            </label>
                            <input type="number" 
                                   class="form-control" 
                                   id="quantidade_retirada" 
                                   name="quantidade_retirada" 
                                   step="0.001" 
                                   min="0.001"
                                   value="<?= $editando && $retirada ? $retirada->getQuantidadeRetirada() : '' ?>" 
                                   required>
                        </div>

                        <!-- Destino -->
                        <div class="col-md-4 mb-3">
                            <label for="destino" class="form-label">
                                <i class="bi bi-geo-alt"></i> Destino *
                            </label>
                            <select class="form-select" id="destino" name="destino" required>
                                <option value="">Selecione...</option>
                                <option value="Pizzaria" <?= ($editando && $retirada && $retirada->getDestino() === 'Pizzaria') ? 'selected' : '' ?>>Pizzaria</option>
                                <option value="Delivery" <?= ($editando && $retirada && $retirada->getDestino() === 'Delivery') ? 'selected' : '' ?>>Delivery</option>
                                <option value="Evento" <?= ($editando && $retirada && $retirada->getDestino() === 'Evento') ? 'selected' : '' ?>>Evento</option>
                                <option value="Consumo Próprio" <?= ($editando && $retirada && $retirada->getDestino() === 'Consumo Próprio') ? 'selected' : '' ?>>Consumo Próprio</option>
                                <option value="Outro" <?= ($editando && $retirada && !in_array($retirada->getDestino(), ['Pizzaria', 'Delivery', 'Evento', 'Consumo Próprio'])) ? 'selected' : '' ?>>Outro</option>
                            </select>
                        </div>

                        <!-- Responsável -->
                        <div class="col-md-4 mb-3">
                            <label for="responsavel" class="form-label">
                                <i class="bi bi-person"></i> Responsável
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="responsavel" 
                                   name="responsavel" 
                                   value="<?= $editando && $retirada ? htmlspecialchars($retirada->getResponsavel() ?? '') : '' ?>"
                                   placeholder="Nome do responsável">
                        </div>

                        <!-- Observações -->
                        <div class="col-12 mb-3">
                            <label for="observacoes" class="form-label">
                                <i class="bi bi-chat-text"></i> Observações
                            </label>
                            <textarea class="form-control" 
                                      id="observacoes" 
                                      name="observacoes" 
                                      rows="3"
                                      placeholder="Observações adicionais sobre a retirada..."><?= $editando && $retirada ? htmlspecialchars($retirada->getObservacoes() ?? '') : '' ?></textarea>
                        </div>
                    </div>

                    <!-- Botões -->
                    <div class="d-flex justify-content-between">
                        <a href="listar.php" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Cancelar
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Dados para JavaScript
        const producoes = <?= json_encode($producoes) ?>;
        const produtosProntos = <?= json_encode($produtosProntos) ?>;
        
        function atualizarItens() {
            const tipoSelect = document.getElementById('tipo_retirada');
            const itemSelect = document.getElementById('item_id');
            const quantidadeInput = document.getElementById('quantidade_retirada');
            
            // Limpar opções
            itemSelect.innerHTML = '<option value="">Selecione um item...</option>';
            
            if (tipoSelect.value === 'producao') {
                // Adicionar produções
                producoes.forEach(producao => {
                    const option = document.createElement('option');
                    option.value = producao.id;
                    option.textContent = `${producao.produto_nome || 'Produto'} - Lote #${producao.lote_id || 'N/A'} (${producao.quantidade_disponivel || 0} porções disponíveis)`;
                    option.dataset.unidade = 'porções';
                    option.dataset.max = producao.quantidade_disponivel || 0;
                    itemSelect.appendChild(option);
                });
            } else if (tipoSelect.value === 'produto_pronto') {
                // Adicionar produtos prontos
                produtosProntos.forEach(produto => {
                    const option = document.createElement('option');
                    option.value = produto.id;
                    option.textContent = `${produto.nome || 'Produto'} (${produto.estoque_disponivel || 0} ${produto.unidade_medida || 'un'} disponíveis)`;
                    option.dataset.unidade = produto.unidade_medida || 'un';
                    option.dataset.max = produto.estoque_disponivel || 0;
                    itemSelect.appendChild(option);
                });
            }
            
            // Atualizar placeholder da quantidade
            quantidadeInput.placeholder = tipoSelect.value === 'producao' ? 'Quantidade em porções' : 'Quantidade';
        }
        
        // Validar quantidade máxima
        document.getElementById('item_id').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const quantidadeInput = document.getElementById('quantidade_retirada');
            
            if (selectedOption.dataset.max) {
                quantidadeInput.max = selectedOption.dataset.max;
                quantidadeInput.placeholder = `Máximo: ${selectedOption.dataset.max} ${selectedOption.dataset.unidade || 'un'}`;
            } else {
                quantidadeInput.removeAttribute('max');
                quantidadeInput.placeholder = 'Quantidade';
            }
        });
        
        // Inicializar se estiver editando
        <?php if ($editando && $retirada): ?>
            atualizarItens();
            setTimeout(() => {
                document.getElementById('item_id').value = '<?= $retirada->getProducaoId() ?? $retirada->getProdutoId() ?>';
            }, 100);
        <?php endif; ?>
    </script>
</body>
</html>

