<?php
/**
 * Relatório de Produção
 * Sistema de Controle de Estoque - Pizzaria
 */

require_once '../../config/config.php';

$erro = '';
$dataInicio = isset($_GET['data_inicio']) ? $_GET['data_inicio'] : date('Y-m-01');
$dataFim = isset($_GET['data_fim']) ? $_GET['data_fim'] : date('Y-m-d');
$produtoId = isset($_GET['produto_id']) && is_numeric($_GET['produto_id']) ? (int) $_GET['produto_id'] : null;

// Buscar dados do relatório
try {
    $relatorio = new Relatorio();
    $dataInicioObj = new DateTime($dataInicio);
    $dataFimObj = new DateTime($dataFim);
    
    $producoes = $relatorio->relatorioProducao($dataInicioObj, $dataFimObj, $produtoId);
    $produtos = Produto::listarAtivos();
} catch (Exception $e) {
    $erro = 'Erro ao gerar relatório: ' . $e->getMessage();
    $producoes = [];
    $produtos = [];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Produção - <?php echo APP_NAME; ?></title>
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
                <i class="fas fa-industry"></i>
                Relatório de Produção
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
                        <label for="produto_id" class="form-label">Produto</label>
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
        <?php if (!empty($producoes)): ?>
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <h4><?php echo count($producoes); ?></h4>
                            <p class="mb-0">Total de Produções</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h4><?php echo array_sum(array_column($producoes, 'quantidade_produzida')); ?></h4>
                            <p class="mb-0">Porções Produzidas</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <h4><?php echo formatMoney(array_sum(array_column($producoes, 'custo_total_producao'))); ?></h4>
                            <p class="mb-0">Custo Total</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body text-center">
                            <h4><?php echo formatNumber(array_sum(array_column($producoes, 'quantidade_materia_prima_usada'))); ?></h4>
                            <p class="mb-0">Matéria-Prima Usada</p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Tabela de produções -->
        <div class="card">
            <div class="card-body">
                <?php if (empty($producoes)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-industry fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Nenhuma produção encontrada no período</h5>
                        <p class="text-muted">Ajuste os filtros ou cadastre novas produções</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Data</th>
                                    <th>Produção #</th>
                                    <th>Produto</th>
                                    <th>Lote #</th>
                                    <th>Qtd. Produzida</th>
                                    <th>Matéria-Prima</th>
                                    <th>Custo Total</th>
                                    <th>Custo/Porção</th>
                                    <th>Estoque</th>
                                    <th>Eficiência</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($producoes as $producao): ?>
                                    <tr>
                                        <td><?php echo formatDate($producao['data_producao']); ?></td>
                                        <td>
                                            <span class="badge bg-info">
                                                #<?php echo $producao['id']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($producao['produto_nome']); ?></strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                #<?php echo $producao['lote_id']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary">
                                                <?php echo $producao['quantidade_produzida']; ?> porções
                                            </span>
                                        </td>
                                        <td>
                                            <?php echo formatNumber($producao['quantidade_materia_prima_usada']); ?>
                                            <small class="text-muted"><?php echo htmlspecialchars($producao['unidade_medida'] ?? ''); ?></small>
                                        </td>
                                        <td><?php echo formatMoney($producao['custo_total_producao']); ?></td>
                                        <td><?php echo formatMoney($producao['custo_por_porcao']); ?></td>
                                        <td>
                                            <?php 
                                            $estoque = $producao['quantidade_disponivel'] ?? $producao['quantidade_produzida'];
                                            $percentual = ($estoque / $producao['quantidade_produzida']) * 100;
                                            $corEstoque = $percentual > 50 ? 'success' : ($percentual > 20 ? 'warning' : 'danger');
                                            ?>
                                            <span class="badge bg-<?php echo $corEstoque; ?>">
                                                <?php echo $estoque; ?> porções
                                            </span>
                                            <small class="text-muted">(<?php echo number_format($percentual, 1); ?>%)</small>
                                        </td>
                                        <td>
                                            <?php 
                                            $eficiencia = $producao['quantidade_produzida'] / $producao['quantidade_materia_prima_usada'];
                                            $corEficiencia = $eficiencia > 5 ? 'success' : ($eficiencia > 3 ? 'warning' : 'danger');
                                            ?>
                                            <span class="badge bg-<?php echo $corEficiencia; ?>">
                                                <?php echo formatNumber($eficiencia, 2); ?> p/un
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        <small class="text-muted">
                            Período: <?php echo formatDate($dataInicio); ?> a <?php echo formatDate($dataFim); ?>
                            <?php if ($produtoId): ?>
                                | Produto: <?php echo htmlspecialchars($produtos[array_search($produtoId, array_column($produtos, 'id'))]->getNome() ?? 'N/A'); ?>
                            <?php endif; ?>
                        </small>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Gráficos -->
        <?php if (!empty($producoes)): ?>
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-chart-line"></i> Produção por Dia</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="graficoProducaoDia" width="400" height="200"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-chart-pie"></i> Distribuição por Produto</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="graficoProducaoProduto" width="400" height="200"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../../assets/js/app.js"></script>

    <?php if (!empty($producoes)): ?>
    <script>
        // Gráfico de produção por dia
        const dadosPorDia = {};
        <?php foreach ($producoes as $producao): ?>
            const data = '<?php echo date('d/m', strtotime($producao['data_producao'])); ?>';
            const quantidade = <?php echo $producao['quantidade_produzida']; ?>;
            
            if (!dadosPorDia[data]) {
                dadosPorDia[data] = 0;
            }
            dadosPorDia[data] += quantidade;
        <?php endforeach; ?>

        const ctx1 = document.getElementById('graficoProducaoDia').getContext('2d');
        new Chart(ctx1, {
            type: 'line',
            data: {
                labels: Object.keys(dadosPorDia),
                datasets: [{
                    label: 'Porções Produzidas',
                    data: Object.values(dadosPorDia),
                    borderColor: '#36A2EB',
                    backgroundColor: 'rgba(54, 162, 235, 0.1)',
                    tension: 0.4
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

        // Gráfico por produto
        const dadosPorProduto = {};
        <?php foreach ($producoes as $producao): ?>
            const produto = '<?php echo addslashes($producao['produto_nome']); ?>';
            const quantidade = <?php echo $producao['quantidade_produzida']; ?>;
            
            if (!dadosPorProduto[produto]) {
                dadosPorProduto[produto] = 0;
            }
            dadosPorProduto[produto] += quantidade;
        <?php endforeach; ?>

        const ctx2 = document.getElementById('graficoProducaoProduto').getContext('2d');
        new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: Object.keys(dadosPorProduto),
                datasets: [{
                    data: Object.values(dadosPorProduto),
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

