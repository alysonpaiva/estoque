<?php
/**
 * Relatório de Entradas (Lotes)
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
    
    $entradas = $relatorio->relatorioEntradas($dataInicioObj, $dataFimObj, $produtoId);
    $produtos = Produto::listarAtivos();
} catch (Exception $e) {
    $erro = 'Erro ao gerar relatório: ' . $e->getMessage();
    $entradas = [];
    $produtos = [];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Entradas - <?php echo APP_NAME; ?></title>
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
                <i class="fas fa-file-import"></i>
                Relatório de Entradas (Lotes)
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
        <?php if (!empty($entradas)): ?>
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <h4><?php echo count($entradas); ?></h4>
                            <p class="mb-0">Total de Lotes</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h4><?php echo formatMoney(array_sum(array_column($entradas, 'preco_compra'))); ?></h4>
                            <p class="mb-0">Valor Investido</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <h4><?php echo formatNumber(array_sum(array_column($entradas, 'quantidade_comprada'))); ?></h4>
                            <p class="mb-0">Quantidade Total</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body text-center">
                            <h4><?php echo formatMoney(array_sum(array_column($entradas, 'preco_compra')) / count($entradas)); ?></h4>
                            <p class="mb-0">Valor Médio/Lote</p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Tabela de entradas -->
        <div class="card">
            <div class="card-body">
                <?php if (empty($entradas)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-file-import fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Nenhuma entrada encontrada no período</h5>
                        <p class="text-muted">Ajuste os filtros ou cadastre novos lotes</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Data</th>
                                    <th>Lote #</th>
                                    <th>Produto</th>
                                    <th>Quantidade</th>
                                    <th>Preço Compra</th>
                                    <th>Custo/Unidade</th>
                                    <th>Estoque Restante</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($entradas as $entrada): ?>
                                    <tr>
                                        <td><?php echo formatDate($entrada['data_compra']); ?></td>
                                        <td>
                                            <span class="badge bg-info">
                                                #<?php echo $entrada['id']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($entrada['produto_nome']); ?></strong>
                                        </td>
                                        <td>
                                            <?php echo formatNumber($entrada['quantidade_comprada']); ?>
                                            <small class="text-muted"><?php echo htmlspecialchars($entrada['unidade_medida'] ?? ''); ?></small>
                                        </td>
                                        <td><?php echo formatMoney($entrada['preco_compra']); ?></td>
                                        <td><?php echo formatMoney($entrada['custo_por_unidade']); ?></td>
                                        <td>
                                            <?php 
                                            $restante = $entrada['quantidade_restante'] ?? $entrada['quantidade_comprada'];
                                            $percentual = ($restante / $entrada['quantidade_comprada']) * 100;
                                            $corEstoque = $percentual > 50 ? 'success' : ($percentual > 20 ? 'warning' : 'danger');
                                            ?>
                                            <span class="badge bg-<?php echo $corEstoque; ?>">
                                                <?php echo formatNumber($restante); ?>
                                            </span>
                                            <small class="text-muted">(<?php echo number_format($percentual, 1); ?>%)</small>
                                        </td>
                                        <td>
                                            <?php if ($restante > 0): ?>
                                                <span class="badge bg-success">Disponível</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Esgotado</span>
                                            <?php endif; ?>
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

        <!-- Gráfico por produto -->
        <?php if (!empty($entradas) && count($produtos) > 1): ?>
            <div class="card mt-4">
                <div class="card-header">
                    <h5><i class="fas fa-chart-pie"></i> Distribuição por Produto</h5>
                </div>
                <div class="card-body">
                    <canvas id="graficoEntradas" width="400" height="200"></canvas>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../../assets/js/app.js"></script>

    <?php if (!empty($entradas) && count($produtos) > 1): ?>
    <script>
        // Agrupar dados por produto
        const dadosPorProduto = {};
        <?php foreach ($entradas as $entrada): ?>
            const produto = '<?php echo addslashes($entrada['produto_nome']); ?>';
            const valor = <?php echo $entrada['preco_compra']; ?>;
            
            if (!dadosPorProduto[produto]) {
                dadosPorProduto[produto] = 0;
            }
            dadosPorProduto[produto] += valor;
        <?php endforeach; ?>

        // Criar gráfico
        const ctx = document.getElementById('graficoEntradas').getContext('2d');
        new Chart(ctx, {
            type: 'pie',
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
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const valor = context.parsed;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentual = ((valor / total) * 100).toFixed(1);
                                return context.label + ': R$ ' + valor.toLocaleString('pt-BR', {minimumFractionDigits: 2}) + ' (' + percentual + '%)';
                            }
                        }
                    }
                }
            }
        });
    </script>
    <?php endif; ?>
</body>
</html>

