<?php
/**
 * Página de Listagem de Lotes
 * Sistema de Controle de Estoque - Pizzaria
 */

require_once '../../config/config.php';

$erro = '';
$sucesso = '';

// Verificar mensagens da URL
if (isset($_GET['erro'])) {
    $erro = sanitizeInput($_GET['erro']);
}
if (isset($_GET['sucesso'])) {
    $sucesso = sanitizeInput($_GET['sucesso']);
}

// Filtros
$produtoId = isset($_GET['produto_id']) && is_numeric($_GET['produto_id']) ? (int) $_GET['produto_id'] : null;
$comEstoque = isset($_GET['com_estoque']) ? true : false;

// Buscar lotes
try {
    if ($produtoId) {
        $lotes = Lote::listarPorProduto($produtoId);
        $produto = Produto::buscarPorId($produtoId);
        $tituloFiltro = $produto ? 'do produto "' . $produto->getNome() . '"' : '';
    } elseif ($comEstoque) {
        $lotes = Lote::listarComEstoque();
        $tituloFiltro = 'com estoque disponível';
    } else {
        $lotes = Lote::listarTodos();
        $tituloFiltro = '';
    }
    
    // Buscar produtos para filtro
    $produtos = Produto::listarAtivos();
} catch (Exception $e) {
    $erro = 'Erro ao carregar lotes: ' . $e->getMessage();
    $lotes = [];
    $produtos = [];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lotes - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="../../index.php">
                <i class="fas fa-boxes"></i> <?php echo APP_NAME; ?>
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="cadastrar.php">
                    <i class="fas fa-plus"></i> Novo Lote
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>
                <i class="fas fa-boxes"></i>
                Lotes Cadastrados <?php echo $tituloFiltro; ?>
            </h2>
            <a href="cadastrar.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Novo Lote
            </a>
        </div>

        <?php if ($erro): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-triangle"></i>
                <?php echo $erro; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($sucesso): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle"></i>
                <?php echo $sucesso; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Filtros -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-6">
                        <label for="produto_id" class="form-label">Filtrar por Produto</label>
                        <select class="form-select" name="produto_id" id="produto_id">
                            <option value="">Todos os produtos</option>
                            <?php foreach ($produtos as $produto): ?>
                                <option value="<?php echo $produto->getId(); ?>"
                                        <?php echo ($produtoId == $produto->getId()) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($produto->getNome()); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="com_estoque" id="com_estoque"
                                   <?php echo $comEstoque ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="com_estoque">
                                Apenas com estoque
                            </label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-outline-primary">
                                <i class="fas fa-filter"></i> Filtrar
                            </button>
                        </div>
                    </div>
                </form>
                <?php if ($produtoId || $comEstoque): ?>
                    <div class="mt-2">
                        <a href="listar.php" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-times"></i> Limpar filtros
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Tabela de lotes -->
        <div class="card">
            <div class="card-body">
                <?php if (empty($lotes)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Nenhum lote encontrado</h5>
                        <a href="cadastrar.php" class="btn btn-primary mt-3">
                            <i class="fas fa-plus"></i> Cadastrar Primeiro Lote
                        </a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Produto</th>
                                    <th>Data Compra</th>
                                    <th>Preço Compra</th>
                                    <th>Qtd. Comprada</th>
                                    <th>Qtd. Restante</th>
                                    <th>Custo/Unidade</th>
                                    <th>Produções</th>
                                    <th width="150">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($lotes as $lote): ?>
                                    <tr>
                                        <td><?php echo $lote->getId(); ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($lote->produtoNome ?? 'N/A'); ?></strong>
                                        </td>
                                        <td><?php echo formatDate($lote->getDataCompra()); ?></td>
                                        <td><?php echo formatMoney($lote->getPrecoCompra()); ?></td>
                                        <td>
                                            <?php echo formatNumber($lote->getQuantidadeComprada()); ?>
                                            <?php 
                                            $produto = $lote->getProduto();
                                            echo $produto ? $produto->getUnidadeMedida() : '';
                                            ?>
                                        </td>
                                        <td>
                                            <?php 
                                            $restante = $lote->getQuantidadeRestante();
                                            $corEstoque = $restante > 0 ? 'success' : 'danger';
                                            ?>
                                            <span class="badge bg-<?php echo $corEstoque; ?>">
                                                <?php echo formatNumber($restante); ?>
                                                <?php echo $produto ? $produto->getUnidadeMedida() : ''; ?>
                                            </span>
                                        </td>
                                        <td><?php echo formatMoney($lote->getCustoPorUnidade()); ?></td>
                                        <td>
                                            <span class="badge bg-info">
                                                <?php echo $lote->getTotalProducoes(); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="cadastrar.php?id=<?php echo $lote->getId(); ?>" 
                                                   class="btn btn-outline-primary" 
                                                   title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="../producao/listar.php?lote_id=<?php echo $lote->getId(); ?>" 
                                                   class="btn btn-outline-info" 
                                                   title="Ver Produções">
                                                    <i class="fas fa-industry"></i>
                                                </a>
                                                <?php if ($restante > 0): ?>
                                                    <a href="../producao/cadastrar.php?lote_id=<?php echo $lote->getId(); ?>" 
                                                       class="btn btn-outline-success" 
                                                       title="Nova Produção">
                                                        <i class="fas fa-plus"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        <small class="text-muted">
                            Total: <?php echo count($lotes); ?> lote(s)
                            <?php if ($tituloFiltro): ?>
                                <?php echo $tituloFiltro; ?>
                            <?php endif; ?>
                        </small>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
</body>
</html>

