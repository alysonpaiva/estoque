<?php
/**
 * Relatório de Retiradas
 * Sistema de Controle de Estoque - Pizzaria
 */

require_once '../../config/config.php';

$erro = '';
$dataInicio = isset($_GET['data_inicio']) ? $_GET['data_inicio'] : date('Y-m-01');
$dataFim = isset($_GET['data_fim']) ? $_GET['data_fim'] : date('Y-m-d');
$destino = isset($_GET['destino']) ? sanitizeInput($_GET['destino']) : '';

// Buscar dados do relatório
try {
    $relatorio = new Relatorio();
    $dataInicioObj = new DateTime($dataInicio);
    $dataFimObj = new DateTime($dataFim);
    
    $retiradas = $relatorio->relatorioRetiradas($dataInicioObj, $dataFimObj, $destino);
    $destinos = Retirada::listarDestinos();
} catch (Exception $e) {
    $erro = 'Erro ao gerar relatório: ' . $e->getMessage();
    $retiradas = [];
    $destinos = [];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Retiradas - <?php echo APP_NAME; ?></title>
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
                <a class="nav-link" href="dashboard.php">
                    <i class="fas fa-chart-bar"></i> Dashboard
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>
                <i class="fas fa-truck"></i>
                Relatório de Retiradas
            </h2>
            <div class="btn-group">
                <a href="dashboard.php" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
                <button onclick="window.print()" class="btn btn-outline-secondary">
                    <i class="fas fa-print"></i> Imprimir
                </button>
            </div>
        </div>

        <?php if ($erro): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i>
                <?php echo $erro; ?>
            </div>
        <?php endif; ?>

        <!-- Filtros -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label for="data_inicio" class="form-label">Data Início</label>
                        <input type="date" class="form-control" name="data_inicio" id="data_inicio" 
                               value="<?php echo $dataInicio; ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label for="data_fim" class="form-label">Data Fim</label>
                        <input type="date" class="form-control" name="data_fim" id="data_fim" 
                               value="<?php echo $dataFim; ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label for="destino" class="form-label">Destino</label>
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
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Filtrar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Resumo -->
        <?php if (!empty($retiradas)): ?>
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <h4><?php echo count($retiradas); ?></h4>
                            <p class="mb-0">Total de Retiradas</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body text-center">
                            <h4><?php echo array_sum(array_column($retiradas, 'quantidade_retirada')); ?></h4>
                            <p class="mb-0">Porções Retiradas</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h4><?php echo formatMoney(array_sum(array_map(function($r) { return $r['quantidade_retirada'] * $r['custo_por_porcao']; }, $retiradas))); ?></h4>
                            <p class="mb-0">Valor Total</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <h4><?php echo count(array_unique(array_column($retiradas, 'destino'))); ?></h4>
                            <p class="mb-0">Destinos Únicos</p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Tabela de retiradas -->
        <div class="card">
            <div class="card-body">
                <?php if (empty($retiradas)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-truck fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Nenhuma retirada encontrada no período</h5>
                        <p class="text-muted">Ajuste os filtros ou cadastre novas retiradas</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Data/Hora</th>
                                    <th>Retirada #</th>
                                    <th>Produto</th>
                                    <th>Produção #</th>
                                    <th>Quantidade</th>
                                    <th>Destino</th>
                                    <th>Responsável</th>
                                    <th>Custo/Porção</th>
                                    <th>Valor Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($retiradas as $retirada): ?>
                                    <tr>
                                        <td><?php echo formatDate($retirada['data_retirada'], DATETIME_FORMAT); ?></td>
                                        <td>
                                            <span class="badge bg-info">
                                                #<?php echo $retirada['id']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($retirada['produto_nome']); ?></strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                #<?php echo $retirada['producao_id']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-warning text-dark">
                                                <?php echo $retirada['quantidade_retirada']; ?> porções
                                            </span>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($retirada['destino']); ?></strong>
                                        </td>
                                        <td><?php echo htmlspecialchars($retirada['responsavel'] ?: '-'); ?></td>
                                        <td><?php echo formatMoney($retirada['custo_por_porcao']); ?></td>
                                        <td>
                                            <strong class="text-success">
                                                <?php echo formatMoney($retirada['quantidade_retirada'] * $retirada['custo_por_porcao']); ?>
                                            </strong>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        <small class="text-muted">
                            Período: <?php echo formatDate($dataInicio); ?> a <?php echo formatDate($dataFim); ?>
                            <?php if ($destino): ?>
                                | Destino: <?php echo htmlspecialchars($destino); ?>
                            <?php endif; ?>
                        </small>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Gráficos -->
        <?php if (!empty($retiradas)): ?>
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-chart-line"></i> Retiradas por Dia</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="graficoRetiradaDia" width="400" height="200"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-chart-pie"></i> Distribuição por Destino</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="graficoRetiradaDestino" width="400" height="200"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ranking de destinos -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5><i class="fas fa-trophy"></i> Ranking de Destinos</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Posição</th>
                                    <th>Destino</th>
                                    <th>Retiradas</th>
                                    <th>Porções</th>
                                    <th>Valor Total</th>
                                    <th>Valor Médio</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $rankingDestinos = [];
                                foreach ($retiradas as $retirada) {
                                    $dest = $retirada['destino'];
                                    if (!isset($rankingDestinos[$dest])) {
                                        $rankingDestinos[$dest] = [
                                            'retiradas' => 0,
                                            'porcoes' => 0,
                                            'valor' => 0
                                        ];
                                    }
                                    $rankingDestinos[$dest]['retiradas']++;
                                    $rankingDestinos[$dest]['porcoes'] += $retirada['quantidade_retirada'];
                                    $rankingDestinos[$dest]['valor'] += $retirada['quantidade_retirada'] * $retirada['custo_por_porcao'];
                                }
                                
                                // Ordenar por valor total
                                uasort($rankingDestinos, function($a, $b) {
                                    return $b['valor'] <=> $a['valor'];
                                });
                                
                                $posicao = 1;
                                foreach ($rankingDestinos as $destino => $dados):
                                ?>
                                    <tr>
                                        <td>
                                            <?php if ($posicao <= 3): ?>
                                                <span class="badge bg-<?php echo $posicao == 1 ? 'warning' : ($posicao == 2 ? 'secondary' : 'dark'); ?>">
                                                    <?php echo $posicao; ?>º
                                                </span>
                                            <?php else: ?>
                                                <?php echo $posicao; ?>º
                                            <?php endif; ?>
                                        </td>
                                        <td><strong><?php echo htmlspecialchars($destino); ?></strong></td>
                                        <td><?php echo $dados['retiradas']; ?></td>
                                        <td><?php echo $dados['porcoes']; ?></td>
                                        <td><?php echo formatMoney($dados['valor']); ?></td>
                                        <td><?php echo formatMoney($dados['valor'] / $dados['retiradas']); ?></td>
                                    </tr>
                                <?php 
                                    $posicao++;
                                endforeach; 
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../../assets/js/app.js"></script>

    <?php if (!empty($retiradas)): ?>
    <script>
        // Gráfico de retiradas por dia
        const dadosPorDia = {};
        <?php foreach ($retiradas as $retirada): ?>
            const data = '<?php echo date('d/m', strtotime($retirada['data_retirada'])); ?>';
            const quantidade = <?php echo $retirada['quantidade_retirada']; ?>;
            
            if (!dadosPorDia[data]) {
                dadosPorDia[data] = 0;
            }
            dadosPorDia[data] += quantidade;
        <?php endforeach; ?>

        const ctx1 = document.getElementById('graficoRetiradaDia').getContext('2d');
        new Chart(ctx1, {
            type: 'bar',
            data: {
                labels: Object.keys(dadosPorDia),
                datasets: [{
                    label: 'Porções Retiradas',
                    data: Object.values(dadosPorDia),
                    backgroundColor: '#FF6384',
                    borderColor: '#FF6384',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Gráfico por destino
        const dadosPorDestino = {};
        <?php foreach ($retiradas as $retirada): ?>
            const destino = '<?php echo addslashes($retirada['destino']); ?>';
            const quantidade = <?php echo $retirada['quantidade_retirada']; ?>;
            
            if (!dadosPorDestino[destino]) {
                dadosPorDestino[destino] = 0;
            }
            dadosPorDestino[destino] += quantidade;
        <?php endforeach; ?>

        const ctx2 = document.getElementById('graficoRetiradaDestino').getContext('2d');
        new Chart(ctx2, {
            type: 'pie',
            data: {
                labels: Object.keys(dadosPorDestino),
                datasets: [{
                    data: Object.values(dadosPorDestino),
                    backgroundColor: [
                        '#FF6384',
                        '#36A2EB',
                        '#FFCE56',
                        '#4BC0C0',
                        '#9966FF',
                        '#FF9F40'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>
    <?php endif; ?>
</body>
</html>

