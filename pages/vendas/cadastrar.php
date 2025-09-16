<?php
/**
 * Página de Cadastro de Vendas Semanais
 * Sistema de Controle de Estoque - Pizzaria
 */

require_once '../../config/config.php';

$erro = '';
$sucesso = '';
$venda = null;

// Verificar se é edição
$editando = false;
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $editando = true;
    $venda = Venda::buscarPorId($_GET['id']);
    if (!$venda) {
        header('Location: listar.php?erro=Venda não encontrada');
        exit;
    }
}

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $semana = (int)($_POST['semana'] ?? 0);
        $ano = (int)($_POST['ano'] ?? 0);
        $valorTotal = convertToDecimal($_POST['valor_total'] ?? '0');
        $observacoes = sanitizeInput($_POST['observacoes'] ?? '');
        
        if ($editando && $venda) {
            // Atualizar venda existente
            $venda->setSemana($semana);
            $venda->setAno($ano);
            $venda->setValorTotal($valorTotal);
            $venda->setObservacoes($observacoes);
        } else {
            // Criar nova venda
            $venda = new Venda($semana, $ano, $valorTotal);
            $venda->setObservacoes($observacoes);
        }
        
        // Validar dados
        $erros = $venda->validar();
        if (!empty($erros)) {
            $erro = implode('<br>', $erros);
        } else {
            // Salvar venda
            if ($venda->salvar()) {
                $sucesso = $editando ? 'Venda atualizada com sucesso!' : 'Venda cadastrada com sucesso!';
                if (!$editando) {
                    // Limpar formulário após inserção
                    $venda = null;
                }
            } else {
                $erro = 'Erro ao salvar venda. Tente novamente.';
            }
        }
    } catch (Exception $e) {
        $erro = 'Erro: ' . $e->getMessage();
    }
}

$pageTitle = ($editando ? 'Editar' : 'Nova') . ' Venda Semanal - ' . APP_NAME;
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
                            <i class="bi bi-bag-check"></i> Produtos Prontos
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="../produtos_prontos/entrada.php">Nova Entrada</a></li>
                            <li><a class="dropdown-item" href="../produtos_prontos/listar_entradas.php">Listar Entradas</a></li>
                            <li><a class="dropdown-item" href="../produtos_prontos/retirada.php">Nova Retirada</a></li>
                            <li><a class="dropdown-item" href="../produtos_prontos/listar_retiradas.php">Listar Retiradas</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown active">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-currency-dollar"></i> Vendas
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item active" href="cadastrar.php">Nova Venda</a></li>
                            <li><a class="dropdown-item" href="listar.php">Listar Vendas</a></li>
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
                <li class="breadcrumb-item"><a href="listar.php">Vendas</a></li>
                <li class="breadcrumb-item active"><?= $editando ? 'Editar' : 'Nova' ?> Venda</li>
            </ol>
        </nav>

        <!-- Título -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3">
                <i class="bi bi-currency-dollar"></i>
                <?= $editando ? 'Editar' : 'Nova' ?> Venda Semanal
            </h1>
            <a href="listar.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Voltar
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

        <!-- Formulário -->
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-clipboard-data"></i>
                            Dados da Venda Semanal
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" class="needs-validation" novalidate>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="semana" class="form-label">
                                            Semana <span class="text-danger">*</span>
                                        </label>
                                        <input type="number" 
                                               class="form-control" 
                                               id="semana" 
                                               name="semana"
                                               min="1"
                                               max="53"
                                               value="<?= $venda ? $venda->getSemana() : Venda::getSemanaAtual() ?>"
                                               required
                                               onchange="atualizarPeriodo()">
                                        <div class="invalid-feedback">
                                            Por favor, informe a semana (1-53).
                                        </div>
                                        <div class="form-text">
                                            Semana do ano (1 a 53)
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="ano" class="form-label">
                                            Ano <span class="text-danger">*</span>
                                        </label>
                                        <input type="number" 
                                               class="form-control" 
                                               id="ano" 
                                               name="ano"
                                               min="2020"
                                               max="2030"
                                               value="<?= $venda ? $venda->getAno() : Venda::getAnoAtual() ?>"
                                               required
                                               onchange="atualizarPeriodo()">
                                        <div class="invalid-feedback">
                                            Por favor, informe o ano.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Período da Semana</label>
                                <div class="form-control-plaintext" id="periodo_semana">
                                    <?= $venda ? $venda->formatarPeriodo() : '' ?>
                                </div>
                                <div class="form-text">
                                    Período calculado automaticamente baseado na semana e ano
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="valor_total" class="form-label">
                                    Valor Total das Vendas <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">R$</span>
                                    <input type="number" 
                                           class="form-control" 
                                           id="valor_total" 
                                           name="valor_total"
                                           step="0.01"
                                           min="0.01"
                                           value="<?= $venda ? number_format($venda->getValorTotal(), 2, '.', '') : '' ?>"
                                           required
                                           placeholder="0,00">
                                </div>
                                <div class="invalid-feedback">
                                    Por favor, informe o valor total das vendas.
                                </div>
                                <div class="form-text">
                                    Valor total de vendas da semana (faturamento bruto)
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="observacoes" class="form-label">
                                    Observações
                                </label>
                                <textarea class="form-control" 
                                          id="observacoes" 
                                          name="observacoes" 
                                          rows="3"
                                          maxlength="500"
                                          placeholder="Observações sobre as vendas da semana..."><?= $venda ? htmlspecialchars($venda->getObservacoes()) : '' ?></textarea>
                                <div class="form-text">
                                    Máximo 500 caracteres
                                </div>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="listar.php" class="btn btn-secondary me-md-2">
                                    <i class="bi bi-x-circle"></i> Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle"></i>
                                    <?= $editando ? 'Atualizar' : 'Cadastrar' ?> Venda
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Sidebar com informações -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="bi bi-info-circle"></i>
                            Informações
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <h6><i class="bi bi-lightbulb"></i> Vendas Semanais</h6>
                            <p class="mb-0">
                                Registre aqui o faturamento semanal da pizzaria para 
                                calcular corretamente o CMV (Custo da Mercadoria Vendida) 
                                e outros indicadores financeiros.
                            </p>
                        </div>

                        <div class="alert alert-warning">
                            <h6><i class="bi bi-exclamation-triangle"></i> Atenção</h6>
                            <p class="mb-0">
                                Só é possível cadastrar uma venda por semana. 
                                Se precisar alterar, edite a venda existente.
                            </p>
                        </div>

                        <div class="alert alert-success">
                            <h6><i class="bi bi-calculator"></i> CMV Automático</h6>
                            <p class="mb-0">
                                Com as vendas cadastradas, o sistema calculará 
                                automaticamente o CMV% no dashboard.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Semana atual -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="bi bi-calendar-week"></i>
                            Semana Atual
                        </h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-1">
                            <strong>Semana:</strong> <?= Venda::getSemanaAtual() ?>
                        </p>
                        <p class="mb-1">
                            <strong>Ano:</strong> <?= Venda::getAnoAtual() ?>
                        </p>
                        <p class="mb-0">
                            <strong>Data:</strong> <?= date('d/m/Y') ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Validação do formulário
        (function() {
            'use strict';
            window.addEventListener('load', function() {
                var forms = document.getElementsByClassName('needs-validation');
                var validation = Array.prototype.filter.call(forms, function(form) {
                    form.addEventListener('submit', function(event) {
                        if (form.checkValidity() === false) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                        form.classList.add('was-validated');
                    }, false);
                });
            }, false);
        })();

        // Atualizar período da semana
        function atualizarPeriodo() {
            const semana = document.getElementById('semana').value;
            const ano = document.getElementById('ano').value;
            
            if (semana && ano) {
                // Calcular período da semana (simplificado)
                const primeiroDia = new Date(ano, 0, 1);
                const diaSemana = primeiroDia.getDay();
                const diasParaPrimeiraSemana = diaSemana === 0 ? 1 : 8 - diaSemana;
                
                const inicioSemana = new Date(ano, 0, diasParaPrimeiraSemana + (semana - 1) * 7);
                const fimSemana = new Date(inicioSemana);
                fimSemana.setDate(fimSemana.getDate() + 6);
                
                const formatarData = (data) => {
                    return data.toLocaleDateString('pt-BR', {
                        day: '2-digit',
                        month: '2-digit',
                        year: 'numeric'
                    });
                };
                
                document.getElementById('periodo_semana').textContent = 
                    formatarData(inicioSemana) + ' a ' + formatarData(fimSemana);
            }
        }

        // Atualizar período inicial
        document.addEventListener('DOMContentLoaded', function() {
            atualizarPeriodo();
        });
    </script>
</body>
</html>

