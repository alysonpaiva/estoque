<?php
/**
 * Página de Listagem de Retiradas de Produtos Prontos
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

// Parâmetros de paginação e busca
$pagina = max(1, (int)($_GET['pagina'] ?? 1));
$itensPorPagina = 20;
$offset = ($pagina - 1) * $itensPorPagina;
$busca = sanitizeInput($_GET['busca'] ?? '');

// Buscar retiradas
try {
    $db = Database::getInstance();
    
    if ($busca) {
        $sql = "SELECT rd.*, p.nome as produto_nome, p.unidade_medida
                FROM retiradas_diretas rd
                JOIN produtos p ON rd.produto_id = p.id
                WHERE p.nome LIKE ? OR rd.destino LIKE ? OR rd.responsavel LIKE ?
                ORDER BY rd.data_retirada DESC 
                LIMIT $itensPorPagina OFFSET $offset";
        $retiradas = $db->fetchAll($sql, ["%$busca%", "%$busca%", "%$busca%"]);
        
        $sqlCount = "SELECT COUNT(*) as total FROM retiradas_diretas rd
                     JOIN produtos p ON rd.produto_id = p.id
                     WHERE p.nome LIKE ? OR rd.destino LIKE ? OR rd.responsavel LIKE ?";
        $totalResult = $db->fetchOne($sqlCount, ["%$busca%", "%$busca%", "%$busca%"]);
        $totalRetiradas = $totalResult['total'] ?? 0;
    } else {
        $sql = "SELECT rd.*, p.nome as produto_nome, p.unidade_medida
                FROM retiradas_diretas rd
                JOIN produtos p ON rd.produto_id = p.id
                ORDER BY rd.data_retirada DESC 
                LIMIT $itensPorPagina OFFSET $offset";
        $retiradas = $db->fetchAll($sql);
        
        $sqlCount = "SELECT COUNT(*) as total FROM retiradas_diretas";
        $totalResult = $db->fetchOne($sqlCount);
        $totalRetiradas = $totalResult['total'] ?? 0;
    }
    
    $totalPaginas = ceil($totalRetiradas / $itensPorPagina);
} catch (Exception $e) {
    $erro = 'Erro ao carregar retiradas: ' . $e->getMessage();
    $retiradas = [];
    $totalRetiradas = 0;
    $totalPaginas = 0;
}

$pageTitle = 'Retiradas de Produtos Prontos - ' . APP_NAME;
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
                            <i class="bi bi-bag-check"></i> Produtos Prontos
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="entrada.php">Nova Entrada</a></li>
                            <li><a class="dropdown-item" href="listar_entradas.php">Listar Entradas</a></li>
                            <li><a class="dropdown-item" href="retirada.php">Nova Retirada</a></li>
                            <li><a class="dropdown-item active" href="listar_retiradas.php">Listar Retiradas</a></li>
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
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="retirada.php">
                            <i class="bi bi-plus-circle"></i> Nova Retirada
                        </a>
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
                <li class="breadcrumb-item"><a href="#">Produtos Prontos</a></li>
                <li class="breadcrumb-item active">Retiradas</li>
            </ol>
        </nav>

        <!-- Título e ações -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3">
                <i class="bi bi-box-arrow-right"></i>
                Retiradas de Produtos Prontos
            </h1>
            <a href="retirada.php" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Nova Retirada
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

        <!-- Filtros e busca -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-8">
                        <label for="busca" class="form-label">Buscar</label>
                        <input type="text" 
                               class="form-control" 
                               id="busca" 
                               name="busca" 
                               value="<?= htmlspecialchars($busca) ?>"
                               placeholder="Buscar por produto, destino ou responsável...">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="bi bi-search"></i> Buscar
                        </button>
                        <?php if ($busca): ?>
                            <a href="listar_retiradas.php" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i> Limpar
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabela de retiradas -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-table"></i>
                    Retiradas Registradas
                    <?php if ($busca): ?>
                        <small class="text-muted">- Filtrado por: "<?= htmlspecialchars($busca) ?>"</small>
                    <?php endif; ?>
                </h5>
            </div>
            <div class="card-body p-0">
                <?php if (empty($retiradas)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-inbox display-1 text-muted"></i>
                        <h4 class="text-muted mt-3">Nenhuma retirada encontrada</h4>
                        <p class="text-muted">
                            <?php if ($busca): ?>
                                Nenhuma retirada corresponde aos critérios de busca.
                            <?php else: ?>
                                Comece registrando a primeira retirada de produto pronto.
                            <?php endif; ?>
                        </p>
                        <a href="retirada.php" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Registrar Primeira Retirada
                        </a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>Data</th>
                                    <th>Produto</th>
                                    <th>Quantidade</th>
                                    <th>Destino</th>
                                    <th>Responsável</th>
                                    <th>Observações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($retiradas as $retirada): ?>
                                    <?php
                                    $dataRetirada = new DateTime($retirada['data_retirada']);
                                    ?>
                                    <tr>
                                        <td>
                                            <small class="text-muted">
                                                <?= $dataRetirada->format('d/m/Y H:i') ?>
                                            </small>
                                        </td>
                                        <td>
                                            <strong><?= htmlspecialchars($retirada['produto_nome']) ?></strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-warning text-dark">
                                                <?= formatNumber($retirada['quantidade_retirada']) ?> <?= $retirada['unidade_medida'] ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">
                                                <?= htmlspecialchars($retirada['destino']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($retirada['responsavel']): ?>
                                                <?= htmlspecialchars($retirada['responsavel']) ?>
                                            <?php else: ?>
                                                <small class="text-muted">-</small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($retirada['observacoes']): ?>
                                                <small class="text-muted">
                                                    <?= htmlspecialchars(substr($retirada['observacoes'], 0, 30)) ?>
                                                    <?= strlen($retirada['observacoes']) > 30 ? '...' : '' ?>
                                                </small>
                                            <?php else: ?>
                                                <small class="text-muted">-</small>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginação -->
                    <?php if ($totalPaginas > 1): ?>
                        <div class="card-footer">
                            <nav aria-label="Paginação de retiradas">
                                <ul class="pagination justify-content-center mb-0">
                                    <?php if ($pagina > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?pagina=<?= $pagina - 1 ?><?= $busca ? '&busca=' . urlencode($busca) : '' ?>">
                                                <i class="bi bi-chevron-left"></i>
                                            </a>
                                        </li>
                                    <?php endif; ?>

                                    <?php
                                    $inicio = max(1, $pagina - 2);
                                    $fim = min($totalPaginas, $pagina + 2);
                                    
                                    for ($i = $inicio; $i <= $fim; $i++):
                                    ?>
                                        <li class="page-item <?= $i == $pagina ? 'active' : '' ?>">
                                            <a class="page-link" href="?pagina=<?= $i ?><?= $busca ? '&busca=' . urlencode($busca) : '' ?>">
                                                <?= $i ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>

                                    <?php if ($pagina < $totalPaginas): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?pagina=<?= $pagina + 1 ?><?= $busca ? '&busca=' . urlencode($busca) : '' ?>">
                                                <i class="bi bi-chevron-right"></i>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                            <div class="text-center mt-2">
                                <small class="text-muted">
                                    Página <?= $pagina ?> de <?= $totalPaginas ?> 
                                    (<?= $totalRetiradas ?> retiradas no total)
                                </small>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

