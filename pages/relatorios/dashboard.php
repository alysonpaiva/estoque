<?php
/**
 * Dashboard - Página Principal CORRIGIDO
 * Sistema de Controle de Estoque - Pizzaria
 */

require_once '../../config/config.php';

$erro = '';

// Carregar dados do dashboard
try {
    $relatorio = new Relatorio();
    $dashboardData = $relatorio->dashboard();
    
    // Estruturar dados de forma segura
    $dashboard = [
        'contadores' => [
            'produtos' => $dashboardData['total_produtos'] ?? 0,
            'lotes' => $dashboardData['total_lotes'] ?? 0,
            'producoes' => $dashboardData['total_producoes'] ?? 0,
            'retiradas' => $dashboardData['total_retiradas'] ?? 0
        ],
        'valores' => [
            'total_investido' => $dashboardData['valor_investido'] ?? 0,
            'total_producao' => $dashboardData['custo_producao'] ?? 0,
            'total_retiradas' => $dashboardData['valor_retiradas'] ?? 0,
            'estoque_atual' => $dashboardData['estoque_porcoes'] ?? 0
        ],
        'alertas' => [
            'produtos_baixo_estoque' => $dashboardData['alertas_estoque'] ?? []
        ],
        'ultimas_movimentacoes' => [
            'lotes' => $dashboardData['ultimos_lotes'] ?? [],
            'producoes' => $dashboardData['ultimas_producoes'] ?? [],
            'retiradas' => $dashboardData['ultimas_retiradas'] ?? []
        ]
    ];
    
    // Calcular CMV (Custo da Mercadoria Vendida)
    // CMV = Estoque Inicial + Compras - Estoque Final
    $estoqueInicial = 0; // Para simplificar, consideramos 0 (pode ser melhorado)
    $compras = $dashboard['valores']['total_investido'];
    $estoqueAtual = $dashboard['valores']['total_producao'] - $dashboard['valores']['total_retiradas'];
    $cmv = $estoqueInicial + $compras - $estoqueAtual;
    $dashboard['valores']['cmv'] = max(0, $cmv); // CMV não pode ser negativo
    
} catch (Exception $e) {
    $erro = 'Erro ao carregar dashboard: ' . $e->getMessage();
    $dashboard = [
        'contadores' => ['produtos' => 0, 'lotes' => 0, 'producoes' => 0, 'retiradas' => 0],
        'valores' => ['total_investido' => 0, 'total_producao' => 0, 'total_retiradas' => 0, 'estoque_atual' => 0],
        'alertas' => ['produtos_baixo_estoque' => []],
        'ultimas_movimentacoes' => ['lotes' => [], 'producoes' => [], 'retiradas' => []]
    ];
}

// Função auxiliar para formatar valores com segurança
function safeFormatMoney($value) {
    if ($value === null || $value === '') {
        return formatMoney(0);
    }
    return formatMoney((float)$value);
}

function safeFormatNumber($value, $decimals = 2) {
    if ($value === null || $value === '') {
        return formatNumber(0, $decimals);
    }
    return formatNumber((float)$value, $decimals);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo APP_NAME; ?></title>
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
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-chart-bar"></i> Relatórios
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="entradas.php">Relatório de Entradas</a></li>
                            <li><a class="dropdown-item" href="producao.php">Relatório de Produção</a></li>
                            <li><a class="dropdown-item" href="retiradas.php">Relatório de Retiradas</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../../index.php">
                            <i class="fas fa-home"></i> Início
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>
                <i class="fas fa-chart-bar"></i>
                Dashboard
            </h2>
            <div class="text-muted">
                Atualizado em <?php echo date('d/m/Y H:i:s'); ?>
            </div>
        </div>

        <?php if ($erro): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i>
                <?php echo $erro; ?>
            </div>
        <?php endif; ?>

        <!-- Cards de Resumo -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="mb-0"><?php echo (int)$dashboard['contadores']['produtos']; ?></h4>
                                <p class="mb-0">Produtos Ativos</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-tags fa-2x"></i>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <a href="../produtos/listar.php" class="text-white text-decoration-none">
                            Ver todos <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="mb-0"><?php echo (int)$dashboard['contadores']['lotes']; ?></h4>
                                <p class="mb-0">Lotes Cadastrados</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-boxes fa-2x"></i>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <a href="../lotes/listar.php" class="text-white text-decoration-none">
                            Ver todos <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="mb-0"><?php echo (int)$dashboard['contadores']['producoes']; ?></h4>
                                <p class="mb-0">Produções</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-industry fa-2x"></i>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <a href="../producao/listar.php" class="text-white text-decoration-none">
                            Ver todas <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="mb-0"><?php echo (int)$dashboard['contadores']['retiradas']; ?></h4>
                                <p class="mb-0">Retiradas</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-truck fa-2x"></i>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <a href="../retiradas/listar.php" class="text-white text-decoration-none">
                            Ver todas <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Valores Financeiros -->
        <div class="row mb-4">
            <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h6 class="card-title text-primary">Total Investido</h6>
                        <h4 class="text-primary"><?php echo safeFormatMoney($dashboard['valores']['total_investido']); ?></h4>
                        <small class="text-muted">Em compras de lotes</small>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h6 class="card-title text-success">Custo Produção</h6>
                        <h4 class="text-success"><?php echo safeFormatMoney($dashboard['valores']['total_producao']); ?></h4>
                        <small class="text-muted">Total produzido</small>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h6 class="card-title text-warning">Valor Retiradas</h6>
                        <h4 class="text-warning"><?php echo safeFormatMoney($dashboard['valores']['total_retiradas']); ?></h4>
                        <small class="text-muted">Total retirado</small>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h6 class="card-title text-danger">CMV</h6>
                        <h4 class="text-danger"><?php echo safeFormatMoney($dashboard['valores']['cmv']); ?></h4>
                        <small class="text-muted">Custo Mercadoria Vendida</small>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h6 class="card-title text-info">Estoque Atual</h6>
                        <h4 class="text-info"><?php echo safeFormatNumber($dashboard['valores']['estoque_atual']); ?> porções</h4>
                        <small class="text-muted">Porções em estoque</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Relatórios Rápidos -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-line"></i>
                            Relatórios Rápidos
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 col-lg-3 mb-3">
                                <a href="entradas.php" class="text-decoration-none">
                                    <div class="card bg-info text-white h-100">
                                        <div class="card-body text-center">
                                            <i class="fas fa-file-import fa-2x mb-2"></i>
                                            <h6>Relatório de Entradas</h6>
                                            <small>Lotes por período</small>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-6 col-lg-3 mb-3">
                                <a href="producao.php" class="text-decoration-none">
                                    <div class="card bg-warning text-white h-100">
                                        <div class="card-body text-center">
                                            <i class="fas fa-industry fa-2x mb-2"></i>
                                            <h6>Relatório de Produção</h6>
                                            <small>Produções por período</small>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-6 col-lg-3 mb-3">
                                <a href="retiradas.php" class="text-decoration-none">
                                    <div class="card bg-danger text-white h-100">
                                        <div class="card-body text-center">
                                            <i class="fas fa-truck fa-2x mb-2"></i>
                                            <h6>Relatório de Retiradas</h6>
                                            <small>Retiradas por período</small>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-6 col-lg-3 mb-3">
                                <a href="../lotes/listar.php" class="text-decoration-none">
                                    <div class="card bg-secondary text-white h-100">
                                        <div class="card-body text-center">
                                            <i class="fas fa-boxes fa-2x mb-2"></i>
                                            <h6>Estoque Atual</h6>
                                            <small>Situação do estoque</small>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Alertas -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-exclamation-triangle text-warning"></i>
                            Alertas de Estoque
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($dashboard['alertas']['produtos_baixo_estoque'])): ?>
                            <div class="text-center text-success">
                                <i class="fas fa-check-circle fa-3x mb-3"></i>
                                <p>Todos os produtos estão com estoque adequado!</p>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <strong>Produtos com estoque baixo:</strong>
                            </div>
                            <div class="list-group list-group-flush">
                                <?php foreach ($dashboard['alertas']['produtos_baixo_estoque'] as $produto): ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <span><?php echo htmlspecialchars($produto['produto_nome'] ?? 'N/A'); ?></span>
                                        <span class="badge bg-warning rounded-pill">
                                            <?php echo safeFormatNumber($produto['estoque_atual'] ?? 0); ?>
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Últimas Movimentações -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-history"></i>
                            Últimas Movimentações
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($dashboard['ultimas_movimentacoes']['lotes']) && 
                                  empty($dashboard['ultimas_movimentacoes']['producoes']) && 
                                  empty($dashboard['ultimas_movimentacoes']['retiradas'])): ?>
                            <div class="text-center text-muted">
                                <i class="fas fa-inbox fa-3x mb-3"></i>
                                <p>Nenhuma movimentação encontrada</p>
                            </div>
                        <?php else: ?>
                            <div class="timeline">
                                <?php 
                                // Combinar e ordenar movimentações
                                $movimentacoes = [];
                                
                                foreach ($dashboard['ultimas_movimentacoes']['lotes'] as $lote) {
                                    $movimentacoes[] = [
                                        'tipo' => 'lote',
                                        'data' => $lote['data_compra'] ?? '',
                                        'descricao' => 'Lote #' . ($lote['id'] ?? '') . ' - ' . ($lote['produto_nome'] ?? 'N/A'),
                                        'valor' => $lote['preco_compra'] ?? 0
                                    ];
                                }
                                
                                foreach ($dashboard['ultimas_movimentacoes']['producoes'] as $producao) {
                                    $movimentacoes[] = [
                                        'tipo' => 'producao',
                                        'data' => $producao['data_producao'] ?? '',
                                        'descricao' => 'Produção #' . ($producao['id'] ?? '') . ' - ' . ($producao['produto_nome'] ?? 'N/A'),
                                        'valor' => $producao['quantidade_produzida'] ?? 0
                                    ];
                                }
                                
                                foreach ($dashboard['ultimas_movimentacoes']['retiradas'] as $retirada) {
                                    $movimentacoes[] = [
                                        'tipo' => 'retirada',
                                        'data' => $retirada['data_retirada'] ?? '',
                                        'descricao' => 'Retirada #' . ($retirada['id'] ?? '') . ' - ' . ($retirada['produto_nome'] ?? 'N/A'),
                                        'valor' => $retirada['quantidade_retirada'] ?? 0
                                    ];
                                }
                                
                                // Ordenar por data
                                usort($movimentacoes, function($a, $b) {
                                    return strtotime($b['data']) - strtotime($a['data']);
                                });
                                
                                // Mostrar apenas as 5 mais recentes
                                $movimentacoes = array_slice($movimentacoes, 0, 5);
                                ?>
                                
                                <?php foreach ($movimentacoes as $mov): ?>
                                    <div class="d-flex mb-3">
                                        <div class="flex-shrink-0">
                                            <?php if ($mov['tipo'] == 'lote'): ?>
                                                <i class="fas fa-plus-circle text-success"></i>
                                            <?php elseif ($mov['tipo'] == 'producao'): ?>
                                                <i class="fas fa-cogs text-info"></i>
                                            <?php else: ?>
                                                <i class="fas fa-minus-circle text-warning"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <div class="fw-bold"><?php echo htmlspecialchars($mov['descricao']); ?></div>
                                            <small class="text-muted">
                                                <?php echo formatDate($mov['data'], DATETIME_FORMAT); ?>
                                            </small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ações Rápidas -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-bolt"></i>
                            Ações Rápidas
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-2">
                                <a href="../produtos/cadastrar.php" class="btn btn-primary w-100">
                                    <i class="fas fa-plus"></i> Novo Produto
                                </a>
                            </div>
                            <div class="col-md-3 mb-2">
                                <a href="../lotes/cadastrar.php" class="btn btn-success w-100">
                                    <i class="fas fa-plus"></i> Novo Lote
                                </a>
                            </div>
                            <div class="col-md-3 mb-2">
                                <a href="../producao/cadastrar.php" class="btn btn-info w-100">
                                    <i class="fas fa-plus"></i> Nova Produção
                                </a>
                            </div>
                            <div class="col-md-3 mb-2">
                                <a href="../retiradas/cadastrar.php" class="btn btn-warning w-100">
                                    <i class="fas fa-plus"></i> Nova Retirada
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/app.js"></script>
</body>
</html>

