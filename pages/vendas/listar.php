<?php
/**
 * Página de Listagem de Vendas Semanais
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

// Buscar vendas
try {
    if ($busca) {
        // Busca por ano ou observações
        $db = Database::getInstance();
        $sql = "SELECT * FROM vendas 
                WHERE ano LIKE ? OR observacoes LIKE ?
                ORDER BY ano DESC, semana DESC 
                LIMIT $itensPorPagina OFFSET $offset";
        $vendas = $db->fetchAll($sql, ["%$busca%", "%$busca%"]);
        
        $sqlCount = "SELECT COUNT(*) as total FROM vendas 
                     WHERE ano LIKE ? OR observacoes LIKE ?";
        $totalResult = $db->fetchOne($sqlCount, ["%$busca%", "%$busca%"]);
        $totalVendas = $totalResult['total'] ?? 0;
    } else {
        $vendas = Venda::listarTodas($itensPorPagina, $offset);
        $totalVendas = Venda::contarTodas();
    }
    
    $totalPaginas = ceil($totalVendas / $itensPorPagina);
} catch (Exception $e) {
    $erro = 'Erro ao carregar vendas: ' . $e->getMessage();
    $vendas = [];
    $totalVendas = 0;
    $totalPaginas = 0;
}

// Calcular estatísticas
$totalFaturamento = 0;
$mediaVendas = 0;
if (!empty($vendas)) {
    foreach ($vendas as $venda) {
        $totalFaturamento += $venda['valor_total'];
    }
    $mediaVendas = $totalFaturamento / count($vendas);
}

$pageTitle = 'Vendas Semanais - ' . APP_NAME;
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
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-truck"></i> Retiradas
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="../retiradas/cadastrar_unificado.php">Nova Retirada</a></li>
                            <li><a class="dropdown-item" href="../retiradas/listar.php">Listar Retiradas</a></li>
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
                <li class="breadcrumb-item active">Vendas Semanais</li>
            </ol>
        </nav>

        <!-- Título e ações -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3">
                <i class="bi bi-currency-dollar"></i>
                Vendas Semanais
            </h1>
            <a href="cadastrar.php" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Nova Venda
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

        <!-- Estatísticas -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title text-primary">Total de Vendas</h5>
                        <h3 class="text-primary"><?= $totalVendas ?></h3>
                        <small class="text-muted">Semanas cadastradas</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title text-success">Faturamento Total</h5>
                        <h3 class="text-success"><?= formatMoney($totalFaturamento) ?></h3>
                        <small class="text-muted">Período exibido</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title text-info">Média Semanal</h5>
                        <h3 class="text-info"><?= formatMoney($mediaVendas) ?></h3>
                        <small class="text-muted">Por semana</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title text-warning">Semana Atual</h5>
                        <h3 class="text-warning"><?= Venda::getSemanaAtual() ?>/<?= Venda::getAnoAtual() ?></h3>
                        <small class="text-muted"><?= date('d/m/Y') ?></small>
                    </div>
                </div>
            </div>
        </div>

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
                               placeholder="Buscar por ano ou observações...">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="bi bi-search"></i> Buscar
                        </button>
                        <?php if ($busca): ?>
                            <a href="listar.php" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i> Limpar
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabela de vendas -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-table"></i>
                    Vendas Cadastradas
                    <?php if ($busca): ?>
                        <small class="text-muted">- Filtrado por: "<?= htmlspecialchars($busca) ?>"</small>
                    <?php endif; ?>
                </h5>
            </div>
            <div class="card-body p-0">
                <?php if (empty($vendas)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-inbox display-1 text-muted"></i>
                        <h4 class="text-muted mt-3">Nenhuma venda encontrada</h4>
                        <p class="text-muted">
                            <?php if ($busca): ?>
                                Nenhuma venda corresponde aos critérios de busca.
                            <?php else: ?>
                                Comece cadastrando a primeira venda semanal.
                            <?php endif; ?>
                        </p>
                        <a href="cadastrar.php" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Cadastrar Primeira Venda
                        </a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>Semana/Ano</th>
                                    <th>Período</th>
                                    <th>Valor Total</th>
                                    <th>Observações</th>
                                    <th>Data Cadastro</th>
                                    <th width="120">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($vendas as $venda): ?>
                                    <?php
                                    $dataInicio = new DateTime($venda['data_inicio']);
                                    $dataFim = new DateTime($venda['data_fim']);
                                    $dataCadastro = new DateTime($venda['data_cadastro']);
                                    ?>
                                    <tr>
                                        <td>
                                            <span class="badge bg-primary fs-6">
                                                <?= $venda['semana'] ?>/<?= $venda['ano'] ?>
                                            </span>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <?= $dataInicio->format('d/m') ?> a <?= $dataFim->format('d/m/Y') ?>
                                            </small>
                                        </td>
                                        <td>
                                            <strong class="text-success">
                                                <?= formatMoney($venda['valor_total']) ?>
                                            </strong>
                                        </td>
                                        <td>
                                            <?php if ($venda['observacoes']): ?>
                                                <small class="text-muted">
                                                    <?= htmlspecialchars(substr($venda['observacoes'], 0, 50)) ?>
                                                    <?= strlen($venda['observacoes']) > 50 ? '...' : '' ?>
                                                </small>
                                            <?php else: ?>
                                                <small class="text-muted">-</small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <?= $dataCadastro->format('d/m/Y H:i') ?>
                                            </small>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="cadastrar.php?id=<?= $venda['id'] ?>" 
                                                   class="btn btn-outline-primary" 
                                                   title="Editar">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <button type="button" 
                                                        class="btn btn-outline-danger" 
                                                        onclick="confirmarExclusao(<?= $venda['id'] ?>, '<?= $venda['semana'] ?>/<?= $venda['ano'] ?>')"
                                                        title="Excluir">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginação -->
                    <?php if ($totalPaginas > 1): ?>
                        <div class="card-footer">
                            <nav aria-label="Paginação de vendas">
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
                                    (<?= $totalVendas ?> vendas no total)
                                </small>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal de confirmação de exclusão -->
    <div class="modal fade" id="modalExclusao" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar Exclusão</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Tem certeza que deseja excluir a venda da semana <strong id="vendaInfo"></strong>?</p>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i>
                        Esta ação não pode ser desfeita e afetará o cálculo do CMV.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form method="POST" action="excluir.php" style="display: inline;">
                        <input type="hidden" name="id" id="vendaId">
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash"></i> Excluir
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmarExclusao(id, info) {
            document.getElementById('vendaId').value = id;
            document.getElementById('vendaInfo').textContent = info;
            new bootstrap.Modal(document.getElementById('modalExclusao')).show();
        }
    </script>
</body>
</html>

