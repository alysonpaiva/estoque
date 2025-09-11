<?php
/**
 * Página de Listagem de Produções
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
$loteId = isset($_GET['lote_id']) && is_numeric($_GET['lote_id']) ? (int) $_GET['lote_id'] : null;
$comEstoque = isset($_GET['com_estoque']) ? true : false;

// Buscar produções
try {
    if ($loteId) {
        $producoes = Producao::listarPorLote($loteId);
        $lote = Lote::buscarPorId($loteId);
        $tituloFiltro = $lote ? 'do lote #' . $lote->getId() : '';
    } elseif ($comEstoque) {
        $producoes = Producao::listarComEstoque();
        $tituloFiltro = 'com estoque disponível';
    } else {
        $producoes = Producao::listarTodas();
        $tituloFiltro = '';
    }
    
    // Buscar lotes para filtro
    $lotes = Lote::listarTodos();
} catch (Exception $e) {
    $erro = 'Erro ao carregar produções: ' . $e->getMessage();
    $producoes = [];
    $lotes = [];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produções - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="../../index.php">
                <i class="fas fa-boxes"></i> <?php echo APP_NAME; ?>
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="cadastrar.php">
                    <i class="fas fa-plus"></i> Nova Produção
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>
                <i class="fas fa-industry"></i>
                Produções Cadastradas <?php echo $tituloFiltro; ?>
            </h2>
            <a href="cadastrar.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nova Produção
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
                        <label for="lote_id" class="form-label">Filtrar por Lote</label>
                        <select class="form-select" name="lote_id" id="lote_id">
                            <option value="">Todos os lotes</option>
                            <?php foreach ($lotes as $lote): ?>
                                <option value="<?php echo $lote->getId(); ?>"
                                        <?php echo ($loteId == $lote->getId()) ? 'selected' : ''; ?>>
                                    Lote #<?php echo $lote->getId(); ?> - <?php echo htmlspecialchars($lote->produtoNome ?? 'Produto'); ?>
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
                <?php if ($loteId || $comEstoque): ?>
                    <div class="mt-2">
                        <a href="listar.php" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-times"></i> Limpar filtros
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Tabela de produções -->
        <div class="card">
            <div class="card-body">
                <?php if (empty($producoes)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-industry fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Nenhuma produção encontrada</h5>
                        <a href="cadastrar.php" class="btn btn-primary mt-3">
                            <i class="fas fa-plus"></i> Cadastrar Primeira Produção
                        </a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Produto</th>
                                    <th>Lote</th>
                                    <th>Data Produção</th>
                                    <th>Qtd. Produzida</th>
                                    <th>Matéria-Prima Usada</th>
                                    <th>Custo Total</th>
                                    <th>Custo/Porção</th>
                                    <th>Estoque</th>
                                    <th width="150">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($producoes as $producao): ?>
                                    <tr>
                                        <td><?php echo $producao->getId(); ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($producao->produtoNome ?? 'N/A'); ?></strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">
                                                #<?php echo $producao->getLoteId(); ?>
                                            </span>
                                        </td>
                                        <td><?php echo formatDate($producao->getDataProducao()); ?></td>
                                        <td>
                                            <span class="badge bg-primary">
                                                <?php echo $producao->getQuantidadeProduzida(); ?> porções
                                            </span>
                                        </td>
                                        <td>
                                            <?php echo formatNumber($producao->getQuantidadeMateriaPrimaUsada()); ?>
                                        </td>
                                        <td><?php echo formatMoney($producao->getCustoTotalProducao()); ?></td>
                                        <td><?php echo formatMoney($producao->getCustoPorPorcao()); ?></td>
                                        <td>
                                            <?php 
                                            $estoque = $producao->getEstoqueDisponivel();
                                            $corEstoque = $estoque > 0 ? 'success' : 'danger';
                                            ?>
                                            <span class="badge bg-<?php echo $corEstoque; ?>">
                                                <?php echo $estoque; ?> porções
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="cadastrar.php?id=<?php echo $producao->getId(); ?>" 
                                                   class="btn btn-outline-primary" 
                                                   title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="../retiradas/listar.php?producao_id=<?php echo $producao->getId(); ?>" 
                                                   class="btn btn-outline-info" 
                                                   title="Ver Retiradas">
                                                    <i class="fas fa-truck"></i>
                                                </a>
                                                <?php if ($estoque > 0): ?>
                                                    <a href="../retiradas/cadastrar.php?producao_id=<?php echo $producao->getId(); ?>" 
                                                       class="btn btn-outline-success" 
                                                       title="Nova Retirada">
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
                            Total: <?php echo count($producoes); ?> produção(ões)
                            <?php if ($tituloFiltro): ?>
                                <?php echo $tituloFiltro; ?>
                            <?php endif; ?>
                        </small>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Resumo -->
        <?php if (!empty($producoes)): ?>
            <div class="row mt-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <h4><?php echo array_sum(array_map(function($p) { return $p->getQuantidadeProduzida(); }, $producoes)); ?></h4>
                            <p class="mb-0">Total Produzido</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h4><?php echo array_sum(array_map(function($p) { return $p->getEstoqueDisponivel(); }, $producoes)); ?></h4>
                            <p class="mb-0">Em Estoque</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <h4><?php echo formatMoney(array_sum(array_map(function($p) { return $p->getCustoTotalProducao(); }, $producoes))); ?></h4>
                            <p class="mb-0">Custo Total</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body text-center">
                            <h4><?php echo formatNumber(array_sum(array_map(function($p) { return $p->getQuantidadeMateriaPrimaUsada(); }, $producoes))); ?></h4>
                            <p class="mb-0">Matéria-Prima Usada</p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/app.js"></script>
</body>
</html>

