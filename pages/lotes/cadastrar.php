<?php
/**
 * Página de Cadastro de Lotes
 * Sistema de Controle de Estoque - Pizzaria
 */

require_once '../../config/config.php';

$erro = '';
$sucesso = '';
$lote = null;

// Verificar se é edição
$editando = false;
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $editando = true;
    $lote = Lote::buscarPorId($_GET['id']);
    if (!$lote) {
        header('Location: listar.php?erro=Lote não encontrado');
        exit;
    }
}

// Buscar produtos ativos para o select
try {
    $produtos = Produto::listarAtivos();
} catch (Exception $e) {
    $erro = 'Erro ao carregar produtos: ' . $e->getMessage();
    $produtos = [];
}

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
         $produtoId = (int)($_POST['produto_id'] ?? 0);
        $precoCompra = convertToDecimal($_POST['preco_compra'] ?? '0');
        $quantidadeComprada = convertToDecimal($_POST['quantidade_comprada'] ?? '0');
        
        if ($editando && $lote) {
            // Atualizar lote existente
            $lote->setProdutoId($produtoId);
            $lote->setPrecoCompra($precoCompra);
            $lote->setQuantidadeComprada($quantidadeComprada);
        } else {
            // Criar novo lote
            $lote = new Lote($produtoId, $precoCompra, $quantidadeComprada);
        }
        
        // Validar dados
        $erros = $lote->validar();
        if (!empty($erros)) {
            $erro = implode('<br>', $erros);
        } else {
            // Salvar lote
            if ($lote->salvar()) {
                $sucesso = $editando ? 'Lote atualizado com sucesso!' : 'Lote cadastrado com sucesso!';
                if (!$editando) {
                    // Limpar formulário após cadastro
                    $lote = null;
                }
            } else {
                $erro = 'Erro ao salvar lote. Tente novamente.';
            }
        }
    } catch (Exception $e) {
        $erro = 'Erro: ' . $e->getMessage();
    }
}

$pageTitle = $editando ? 'Editar Lote' : 'Cadastrar Lote';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="../../index.php">
                <i class="fas fa-boxes"></i> <?php echo APP_NAME; ?>
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="listar.php">
                    <i class="fas fa-list"></i> Listar Lotes
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">
                            <i class="fas fa-<?php echo $editando ? 'edit' : 'plus'; ?>"></i>
                            <?php echo $pageTitle; ?>
                        </h4>
                    </div>
                    <div class="card-body">
                        <?php if (empty($produtos)): ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                Nenhum produto ativo encontrado. 
                                <a href="../produtos/cadastrar.php">Cadastre um produto</a> primeiro.
                            </div>
                        <?php else: ?>
                            <?php if ($erro): ?>
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <?php echo $erro; ?>
                                </div>
                            <?php endif; ?>

                            <?php if ($sucesso): ?>
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle"></i>
                                    <?php echo $sucesso; ?>
                                </div>
                            <?php endif; ?>

                            <form method="POST" class="needs-validation" novalidate>
                                <div class="mb-3">
                                    <label for="produto_id" class="form-label">
                                        Produto <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" id="produto_id" name="produto_id" required>
                                        <option value="">Selecione o produto...</option>
                                        <?php foreach ($produtos as $produto): ?>
                                            <option value="<?php echo $produto->getId(); ?>"
                                                    data-unidade="<?php echo htmlspecialchars($produto->getUnidadeMedida()); ?>"
                                                    <?php echo ($lote && $lote->getProdutoId() == $produto->getId()) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($produto->getNome()); ?>
                                                (<?php echo htmlspecialchars($produto->getUnidadeMedida()); ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="invalid-feedback">
                                        Por favor, selecione um produto.
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="preco_compra" class="form-label">
                                                Preço de Compra (R$) <span class="text-danger">*</span>
                                            </label>
                                            <div class="input-group">
                                                <span class="input-group-text">R$</span>
                                                <input type="text" 
                                                       class="form-control" 
                                                       id="preco_compra" 
                                                       name="preco_compra" 
                                                       value="<?php echo $lote ? convertFromDecimal($lote->getPrecoCompra()) : ''; ?>"
                                                       placeholder="0,00"
                                                       required>
                                            </div>
                                            <div class="invalid-feedback">
                                                Por favor, informe o preço de compra.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="quantidade_comprada" class="form-label">
                                                Quantidade Comprada <span class="text-danger">*</span>
                                                <span id="unidade_display" class="text-muted"></span>
                                            </label>
                                            <input type="text" 
                                                   class="form-control" 
                                                   id="quantidade_comprada" 
                                                   name="quantidade_comprada" 
                                                   value="<?php echo $lote ? convertFromDecimal($lote->getQuantidadeComprada()) : ''; ?>"
                                                   placeholder="0,000"
                                                   required>
                                            <div class="invalid-feedback">
                                                Por favor, informe a quantidade comprada.
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Cálculos automáticos -->
                                <div class="card bg-light mb-3">
                                    <div class="card-body">
                                        <h6 class="card-title">
                                            <i class="fas fa-calculator"></i>
                                            Cálculos Automáticos
                                        </h6>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <p class="mb-1">
                                                    <strong>Custo por Unidade:</strong>
                                                    <span id="custo_unidade" class="text-primary">R$ 0,00</span>
                                                </p>
                                            </div>
                                            <div class="col-md-6">
                                                <p class="mb-1">
                                                    <strong>Custo por Kg:</strong>
                                                    <span id="custo_kg" class="text-info">R$ 0,00</span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <a href="listar.php" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left"></i> Voltar
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i>
                                        <?php echo $editando ? 'Atualizar' : 'Cadastrar'; ?>
                                    </button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if ($editando && $lote): ?>
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-info-circle"></i>
                                Informações do Lote
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Data de Compra:</strong> 
                                       <?php echo formatDate($lote->getDataCompra()); ?></p>
                                    <p><strong>Quantidade Restante:</strong> 
                                       <?php echo formatNumber($lote->getQuantidadeRestante()); ?>
                                       <?php 
                                       $produto = $lote->getProduto();
                                       echo $produto ? $produto->getUnidadeMedida() : '';
                                       ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Total de Produções:</strong> 
                                       <?php echo $lote->getTotalProducoes(); ?></p>
                                    <p><strong>Custo por Unidade:</strong> 
                                       <?php echo formatMoney($lote->getCustoPorUnidade()); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
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

        // Formatação de números
        function formatarNumero(input) {
            let valor = input.value.replace(/[^\d,]/g, '');
            valor = valor.replace(/,/g, '.');
            if (valor && !isNaN(valor)) {
                input.value = parseFloat(valor).toFixed(3).replace('.', ',');
            }
        }

        function formatarMoeda(input) {
            let valor = input.value.replace(/[^\d,]/g, '');
            valor = valor.replace(/,/g, '.');
            if (valor && !isNaN(valor)) {
                input.value = parseFloat(valor).toFixed(2).replace('.', ',');
            }
        }

        // Aplicar formatação
        document.getElementById('preco_compra').addEventListener('blur', function() {
            formatarMoeda(this);
            calcularCustos();
        });

        document.getElementById('quantidade_comprada').addEventListener('blur', function() {
            formatarNumero(this);
            calcularCustos();
        });

        document.getElementById('peso_unidade').addEventListener('blur', function() {
            if (this.value) {
                formatarNumero(this);
            }
            calcularCustos();
        });

        // Atualizar unidade de medida
        document.getElementById('produto_id').addEventListener('change', function() {
            const option = this.options[this.selectedIndex];
            const unidade = option.getAttribute('data-unidade');
            document.getElementById('unidade_display').textContent = unidade ? `(${unidade})` : '';
            calcularCustos();
        });

        // Calcular custos automaticamente
        function calcularCustos() {
            const preco = parseFloat(document.getElementById('preco_compra').value.replace(',', '.')) || 0;
            const quantidade = parseFloat(document.getElementById('quantidade_comprada').value.replace(',', '.')) || 0;
            const peso = parseFloat(document.getElementById('peso_unidade').value.replace(',', '.')) || 0;

            let custoUnidade = 0;
            let custoKg = 0;

            if (preco > 0 && quantidade > 0) {
                custoUnidade = preco / quantidade;
                
                if (peso > 0) {
                    custoKg = custoUnidade / peso;
                }
            }

            document.getElementById('custo_unidade').textContent = 
                'R$ ' + custoUnidade.toFixed(4).replace('.', ',');
            
            document.getElementById('custo_kg').textContent = 
                peso > 0 ? 'R$ ' + custoKg.toFixed(2).replace('.', ',') : 'N/A';
        }

        // Calcular custos na inicialização
        document.addEventListener('DOMContentLoaded', function() {
            const produtoSelect = document.getElementById('produto_id');
            if (produtoSelect.value) {
                const option = produtoSelect.options[produtoSelect.selectedIndex];
                const unidade = option.getAttribute('data-unidade');
                document.getElementById('unidade_display').textContent = unidade ? `(${unidade})` : '';
            }
            calcularCustos();
        });
    </script>
</body>
</html>

