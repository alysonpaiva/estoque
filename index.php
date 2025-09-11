<?php
/**
 * Página Principal do Sistema
 * Sistema de Controle de Estoque - Pizzaria
 */

require_once 'config/config.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 80px 0;
        }
        .feature-card {
            transition: transform 0.3s;
            height: 100%;
        }
        .feature-card:hover {
            transform: translateY(-5px);
        }
        .action-card {
            border: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.3s;
        }
        .action-card:hover {
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold mb-4">
                        <i class="fas fa-boxes me-3"></i>
                        Sistema de Controle de Estoque
                    </h1>
                    <p class="lead mb-4">
                        Controle completo do seu estoque com rastreabilidade por lotes, 
                        produção vinculada e relatórios detalhados.
                    </p>
                    <a href="pages/relatorios/dashboard.php" class="btn btn-light btn-lg">
                        <i class="fas fa-chart-bar me-2"></i>
                        Acessar Dashboard
                    </a>
                </div>
                <div class="col-lg-6 text-center">
                    <i class="fas fa-warehouse" style="font-size: 200px; opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5">
        <div class="container">
            <div class="row text-center mb-5">
                <div class="col-12">
                    <h2 class="display-5 fw-bold">Funcionalidades Principais</h2>
                    <p class="lead text-muted">Tudo que você precisa para controlar seu estoque</p>
                </div>
            </div>
            
            <div class="row g-4">
                <div class="col-md-6 col-lg-3">
                    <div class="card feature-card text-center p-4">
                        <div class="card-body">
                            <i class="fas fa-tags fa-3x text-primary mb-3"></i>
                            <h5 class="card-title">Cadastro de Produtos</h5>
                            <p class="card-text">Gerencie seus produtos com informações completas e controle de ativação.</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-3">
                    <div class="card feature-card text-center p-4">
                        <div class="card-body">
                            <i class="fas fa-boxes fa-3x text-success mb-3"></i>
                            <h5 class="card-title">Controle de Lotes</h5>
                            <p class="card-text">Rastreie cada lote com preço, quantidade e custo por unidade automático.</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-3">
                    <div class="card feature-card text-center p-4">
                        <div class="card-body">
                            <i class="fas fa-industry fa-3x text-info mb-3"></i>
                            <h5 class="card-title">Produção Vinculada</h5>
                            <p class="card-text">Cada produção vinculada ao lote específico com cálculo automático de custos.</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-3">
                    <div class="card feature-card text-center p-4">
                        <div class="card-body">
                            <i class="fas fa-truck fa-3x text-warning mb-3"></i>
                            <h5 class="card-title">Controle de Retiradas</h5>
                            <p class="card-text">Registre retiradas com destino, responsável e baixa automática no estoque.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Actions Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row text-center mb-5">
                <div class="col-12">
                    <h2 class="display-5 fw-bold">Ações Rápidas</h2>
                    <p class="lead text-muted">Acesse rapidamente as principais funcionalidades</p>
                </div>
            </div>
            
            <div class="row g-4">
                <div class="col-md-6 col-lg-4">
                    <div class="card action-card h-100">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-plus-circle fa-3x text-primary mb-3"></i>
                            <h5 class="card-title">Novo Produto</h5>
                            <p class="card-text">Cadastre um novo produto no sistema</p>
                            <a href="pages/produtos/cadastrar.php" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Cadastrar
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4">
                    <div class="card action-card h-100">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-box fa-3x text-success mb-3"></i>
                            <h5 class="card-title">Novo Lote</h5>
                            <p class="card-text">Registre a compra de um novo lote</p>
                            <a href="pages/lotes/cadastrar.php" class="btn btn-success">
                                <i class="fas fa-plus me-2"></i>Registrar
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4">
                    <div class="card action-card h-100">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-cogs fa-3x text-info mb-3"></i>
                            <h5 class="card-title">Nova Produção</h5>
                            <p class="card-text">Registre uma nova produção</p>
                            <a href="pages/producao/cadastrar.php" class="btn btn-info">
                                <i class="fas fa-plus me-2"></i>Produzir
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4">
                    <div class="card action-card h-100">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-truck-loading fa-3x text-warning mb-3"></i>
                            <h5 class="card-title">Nova Retirada</h5>
                            <p class="card-text">Registre uma retirada para pizzaria</p>
                            <a href="pages/retiradas/cadastrar.php" class="btn btn-warning">
                                <i class="fas fa-plus me-2"></i>Retirar
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4">
                    <div class="card action-card h-100">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-chart-bar fa-3x text-danger mb-3"></i>
                            <h5 class="card-title">Relatórios</h5>
                            <p class="card-text">Visualize relatórios e dashboard</p>
                            <a href="pages/relatorios/dashboard.php" class="btn btn-danger">
                                <i class="fas fa-chart-line me-2"></i>Ver Relatórios
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4">
                    <div class="card action-card h-100">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-list fa-3x text-secondary mb-3"></i>
                            <h5 class="card-title">Listar Produtos</h5>
                            <p class="card-text">Visualize todos os produtos cadastrados</p>
                            <a href="pages/produtos/listar.php" class="btn btn-secondary">
                                <i class="fas fa-list me-2"></i>Listar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="py-5">
        <div class="container">
            <div class="row text-center">
                <div class="col-12 mb-5">
                    <h2 class="display-5 fw-bold">Resumo do Sistema</h2>
                </div>
            </div>
            
            <div class="row g-4">
                <?php
                try {
                    $relatorio = new Relatorio();
                    $dashboard = $relatorio->dashboard();
                ?>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="fas fa-tags fa-2x text-primary mb-2"></i>
                            <h3 class="text-primary"><?php echo $dashboard['total_produtos']; ?></h3>
                            <p class="mb-0">Produtos Ativos</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="fas fa-boxes fa-2x text-success mb-2"></i>
                            <h3 class="text-success"><?php echo $dashboard['total_lotes']; ?></h3>
                            <p class="mb-0">Lotes Cadastrados</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="fas fa-industry fa-2x text-info mb-2"></i>
                            <h3 class="text-info"><?php echo $dashboard['total_producoes']; ?></h3>
                            <p class="mb-0">Produções Realizadas</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="fas fa-truck fa-2x text-warning mb-2"></i>
                            <h3 class="text-warning"><?php echo $dashboard['total_retiradas']; ?></h3>
                            <p class="mb-0">Retiradas Efetuadas</p>
                        </div>
                    </div>
                </div>
                <?php
                } catch (Exception $e) {
                    echo '<div class="col-12"><div class="alert alert-warning">Dados não disponíveis. Configure o banco de dados.</div></div>';
                }
                ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><i class="fas fa-boxes me-2"></i><?php echo APP_NAME; ?></h5>
                    <p class="mb-0">Sistema completo de controle de estoque para pizzarias.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0">Versão <?php echo APP_VERSION; ?></p>
                    <small class="text-muted">Desenvolvido com PHP orientado a objetos</small>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

