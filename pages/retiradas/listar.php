<?php
/**
 * Página de Listagem de Retiradas
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
$producaoId = isset($_GET['producao_id']) && is_numeric($_GET['producao_id']) ? (int) $_GET['producao_id'] : null;
$destino = isset($_GET['destino']) ? sanitizeInput($_GET['destino']) : '';

// Buscar retiradas
try {
    if ($producaoId) {
        $retiradas = Retirada::listarPorProducao($producaoId);
        $producao = Producao::buscarPorId($producaoId);
        $tituloFiltro = $producao ? 'da produção #' . $producao->getId() : '';
    } elseif ($destino) {
        $retiradas = Retirada::listarPorDestino($destino);
        $tituloFiltro = 'para "' . $destino . '"';
    } else {
        $retiradas = Retirada::listarTodas();
        $tituloFiltro = '';
    }
    
    // Buscar produções para filtro
    $producoes = Producao::listarTodas();
    
    // Buscar destinos únicos
    $destinos = Retirada::listarDestinos();
} catch (Exception $e) {
    $erro = 'Erro ao carregar retiradas: ' . $e->getMessage();
    $retiradas = [];
    $producoes = [];
    $destinos = [];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Retiradas - <?php echo APP_NAME; ?></title>
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
                    <i class="fas fa-plus"></i> Nova Retirada
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>
                <i class="fas fa-truck"></i>
                Retiradas Cadastradas <?php echo $tituloFiltro; ?>
            </h2>
            <a href="cadastrar.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nova Retirada
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
                    <div class="col-md-4">
                        <label for="producao_id" class="form-label">Filtrar por Produção</label>
                        <select class="form-select" name="producao_id" id="producao_id">
                            <option value="">Todas as produções</option>
                            <?php foreach ($producoes as $producao): ?>
                                <option value="<?php echo $producao->getId(); ?>"
                                        <?php echo ($producaoId == $producao->getId()) ? 'selected' : ''; ?>>
                                    Produção #<?php echo $producao->getId(); ?> - <?php echo htmlspecialchars($producao->produtoNome ?? 'Produto'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="destino" class="form-label">Filtrar por Destino</label>
                        <select class="form-select" name="destino" id="destino">
                            <option value="">Todos os destinos</option>
                            <?php foreach ($destinos as $dest): ?>
                                <option value="<?php echo htmlspecialchars($dest); ?>"
                                        <?php echo ($destino == $dest) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($dest); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-outline-primary">
                                <i class="fas fa-filter"></i> Filtrar
                            </button>
                        </div>
                    </div>
                </form>
                <?php if ($producaoId || $destino): ?>
                    <div class="mt-2">
                        <a href="listar.php" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-times"></i> Limpar filtros
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Tabela de retiradas -->
        <div class="card">
            <div class="card-body">
                <?php if (empty($retiradas)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-truck fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Nenhuma retirada encontrada</h5>
                        <a href="cadastrar.php" class="btn btn-primary mt-3">
                            <i class="fas fa-plus"></i> Cadastrar Primeira Retirada
                        </a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Produto</th>
                                    <th>Produção</th>
                                    <th>Data Retirada</th>
                                    <th>Quantidade</th>
                                    <th>Destino</th>
                                    <th>Responsável</th>
                                    <th>Valor Total</th>
                                    <th width="100">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($retiradas as $retirada): ?>
                                    <tr>
                                        <td><?php echo $retirada->getId(); ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($retirada->produtoNome ?? 'N/A'); ?></strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">
                                                #<?php echo $retirada->getProducaoId(); ?>
                                            </span>
                                        </td>
                                        <td><?php echo formatDate($retirada->getDataRetirada(), DATETIME_FORMAT); ?></td>
                                        <td>
                                            <span class="badge bg-warning text-dark">
                                                <?php echo $retirada->getQuantidadeRetirada(); ?> porções
                                            </span>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($retirada->getDestino()); ?></strong>
                                        </td>
                                        <td><?php echo htmlspecialchars($retirada->getResponsavel() ?: '-'); ?></td>
                                        <td>
                                            <strong class="text-success">
                                                <?php echo formatMoney($retirada->calcularValor()); ?>
                                            </strong>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="cadastrar.php?id=<?php echo $retirada->getId(); ?>" 
                                                   class="btn btn-outline-primary" 
                                                   title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="../producao/listar.php?producao_id=<?php echo $retirada->getProducaoId(); ?>" 
                                                   class="btn btn-outline-info" 
                                                   title="Ver Produção">
                                                    <i class="fas fa-industry"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        <small class="text-muted">
                            Total: <?php echo count($retiradas); ?> retirada(s)
                            <?php if ($tituloFiltro): ?>
                                <?php echo $tituloFiltro; ?>
                            <?php endif; ?>
                        </small>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Resumo -->
        <?php if (!empty($retiradas)): ?>
            <div class="row mt-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <h4><?php echo count($retiradas); ?></h4>
                            <p class="mb-0">Total Retiradas</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body text-center">
                            <h4><?php echo array_sum(array_map(function($r) { return $r->getQuantidadeRetirada(); }, $retiradas)); ?></h4>
                            <p class="mb-0">Porções Retiradas</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h4><?php echo formatMoney(array_sum(array_map(function($r) { return $r->calcularValor(); }, $retiradas))); ?></h4>
                            <p class="mb-0">Valor Total</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <h4><?php echo count(array_unique(array_map(function($r) { return $r->getDestino(); }, $retiradas))); ?></h4>
                            <p class="mb-0">Destinos Únicos</p>
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

