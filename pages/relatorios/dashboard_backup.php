<?php
/**
 * Dashboard - Página Principal
 * Sistema de Controle de Estoque - Pizzaria
 */

require_once '../../config/config.php';

$erro = '';

// Carregar dados do dashboard
try {
    $relatorio = new Relatorio();
    $dashboard = $relatorio->dashboard();
} catch (Exception $e) {
    $erro = 'Erro ao carregar dashboard: ' . $e->getMessage();
    $dashboard = [
        'contadores' => ['produtos' => 0, 'lotes' => 0, 'producoes' => 0, 'retiradas' => 0],
        'valores' => ['total_investido' => 0, 'total_producao' => 0, 'total_retiradas' => 0, 'estoque_atual' => 0],
        'alertas' => ['produtos_baixo_estoque' => []],
        'ultimas_movimentacoes' => []
    ];
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
    <link href="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.css" rel="stylesheet">
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
                            <li><a class="dropdown-item" href="entradas.php">Entradas</a></li>
                            <li><a class="dropdown-item" href="producao.php">Produção</a></li>
                            <li><a class="dropdown-item" href="retiradas.php">Retiradas</a></li>
                            <li><a class="dropdown-item" href="estoque.php">Estoque Atual</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-cog"></i> Cadastros
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="../produtos/listar.php">Produtos</a></li>
                            <li><a class="dropdown-item" href="../lotes/listar.php">Lotes</a></li>
                            <li><a class="dropdown-item" href="../producao/listar.php">Produção</a></li>
                            <li><a class="dropdown-item" href="../retiradas/listar.php">Retiradas</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>
                <i class="fas fa-tachometer-alt"></i>
                Dashboard
            </h2>
            <div class="text-muted">
                <i class="fas fa-clock"></i>
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
                                <h4 class="mb-0"><?php echo $dashboard['contadores']['produtos']; ?></h4>
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
                                <h4 class="mb-0"><?php echo $dashboard['contadores']['lotes']; ?></h4>
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
                                <h4 class="mb-0"><?php echo $dashboard['contadores']['producoes']; ?></h4>
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
                                <h4 class="mb-0"><?php echo $dashboard['contadores']['retiradas']; ?></h4>
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
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title text-primary">Total Investido</h5>
                        <h3 class="text-primary"><?php echo formatMoney($dashboard['valores']['total_investido']); ?></h3>
                        <small class="text-muted">Em compras de lotes</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title text-success">Custo Produção</h5>
                        <h3 class="text-success"><?php echo formatMoney($dashboard['valores']['total_producao']); ?></h3>
                        <small class="text-muted">Total produzido</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title text-warning">Valor Retiradas</h5>
                        <h3 class="text-warning"><?php echo formatMoney($dashboard['valores']['total_retiradas']); ?></h3>
                        <small class="text-muted">Total retirado</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title text-info">Estoque Atual</h5>
                        <h3 class="text-info"><?php echo formatMoney($dashboard['valores']['estoque_atual']); ?></h3>
                        <small class="text-muted">Valor em estoque</small>
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
                                        <span><?php echo htmlspecialchars($produto['nome']); ?></span>
                                        <span class="badge bg-warning rounded-pill">
                                            <?php echo formatNumber($produto['estoque_total']); ?>
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
                        <?php if (empty($dashboard['ultimas_movimentacoes'])): ?>
                            <div class="text-center text-muted">
                                <i class="fas fa-inbox fa-3x mb-3"></i>
                                <p>Nenhuma movimentação encontrada</p>
                            </div>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach (array_slice($dashboard['ultimas_movimentacoes'], 0, 8) as $mov): ?>
                                    <div class="list-group-item">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1">
                                                <?php
                                                $iconClass = '';
                                                $badgeClass = '';
                                                switch ($mov['tipo_movimentacao']) {
                                                    case 'ENTRADA':
                                                        $iconClass = 'fas fa-arrow-down text-success';
                                                        $badgeClass = 'bg-success';
                                                        break;
                                                    case 'PRODUCAO':
                                                        $iconClass = 'fas fa-industry text-info';
                                                        $badgeClass = 'bg-info';
                                                        break;
                                                    case 'RETIRADA':
                                                        $iconClass = 'fas fa-arrow-up text-warning';
                                                        $badgeClass = 'bg-warning';
                                                        break;
                                                }
                                                ?>
                                                <i class="<?php echo $iconClass; ?>"></i>
                                                <?php echo htmlspecialchars($mov['produto_nome']); ?>
                                                <span class="badge <?php echo $badgeClass; ?> ms-2">
                                                    <?php echo $mov['tipo_movimentacao']; ?>
                                                </span>
                                            </h6>
                                            <small><?php echo formatDate($mov['data_movimentacao'], DATETIME_FORMAT); ?></small>
                                        </div>
                                        <p class="mb-1"><?php echo htmlspecialchars($mov['descricao']); ?></p>
                                        <small class="text-muted">
                                            Quantidade: <?php echo formatNumber($mov['quantidade']); ?> | 
                                            Valor: <?php echo formatMoney($mov['valor']); ?>
                                        </small>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráfico de Valores -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-pie"></i>
                            Distribuição de Valores
                        </h5>
                    </div>
                    <div class="card-body">
                        <canvas id="chartValores" width="400" height="200"></canvas>
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
                                <a href="../produtos/cadastrar.php" class="btn btn-outline-primary w-100">
                                    <i class="fas fa-plus"></i> Novo Produto
                                </a>
                            </div>
                            <div class="col-md-3 mb-2">
                                <a href="../lotes/cadastrar.php" class="btn btn-outline-success w-100">
                                    <i class="fas fa-box"></i> Novo Lote
                                </a>
                            </div>
                            <div class="col-md-3 mb-2">
                                <a href="../producao/cadastrar.php" class="btn btn-outline-info w-100">
                                    <i class="fas fa-industry"></i> Nova Produção
                                </a>
                            </div>
                            <div class="col-md-3 mb-2">
                                <a href="../retiradas/cadastrar.php" class="btn btn-outline-warning w-100">
                                    <i class="fas fa-truck"></i> Nova Retirada
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script>
        // Gráfico de distribuição de valores
        const ctx = document.getElementById('chartValores').getContext('2d');
        const chart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Investido', 'Produção', 'Retiradas', 'Estoque'],
                datasets: [{
                    data: [
                        <?php echo $dashboard['valores']['total_investido']; ?>,
                        <?php echo $dashboard['valores']['total_producao']; ?>,
                        <?php echo $dashboard['valores']['total_retiradas']; ?>,
                        <?php echo $dashboard['valores']['estoque_atual']; ?>
                    ],
                    backgroundColor: [
                        '#0d6efd',
                        '#198754',
                        '#ffc107',
                        '#0dcaf0'
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const value = context.parsed;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((value / total) * 100).toFixed(1);
                                return context.label + ': R$ ' + value.toLocaleString('pt-BR', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                }) + ' (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });

        // Auto-refresh da página a cada 5 minutos
        setTimeout(function() {
            location.reload();
        }, 300000);
    </script>
</body>
</html>

