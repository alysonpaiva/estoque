<?php
/**
 * Página de Cadastro de Produção
 * Sistema de Controle de Estoque - Pizzaria
 */

require_once '../../config/config.php';

$erro = '';
$sucesso = '';
$producao = null;

// Verificar se é edição
$editando = false;
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $editando = true;
    $producao = Producao::buscarPorId($_GET['id']);
    if (!$producao) {
        header('Location: listar.php?erro=Produção não encontrada');
        exit;
    }
}

// Buscar lotes com estoque para o select
try {
    $lotes = Lote::listarComEstoque();
} catch (Exception $e) {
    $erro = 'Erro ao carregar lotes: ' . $e->getMessage();
    $lotes = [];
}

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $loteId = (int) ($_POST['lote_id'] ?? 0);
        $quantidadeProduzida = (int) ($_POST['quantidade_produzida'] ?? 0);
        $quantidadeMateriaPrima = convertToDecimal($_POST['quantidade_materia_prima'] ?? '0');
        $observacoes = sanitizeInput($_POST['observacoes'] ?? '');
        
        if ($editando && $producao) {
            // Atualizar produção existente
            $producao->setLoteId($loteId);
            $producao->setQuantidadeProduzida($quantidadeProduzida);
            $producao->setQuantidadeMateriaPrimaUsada($quantidadeMateriaPrima);
            $producao->setObservacoes($observacoes);
            $producao->calcularCustos();
        } else {
            // Criar nova produção
            $producao = new Producao($loteId, $quantidadeProduzida, $quantidadeMateriaPrima);
            $producao->setObservacoes($observacoes);
        }
        
        // Validar dados
        $erros = $producao->validar();
        if (!empty($erros)) {
            $erro = implode('<br>', $erros);
        } else {
            // Salvar produção
            if ($producao->salvar()) {
                $sucesso = $editando ? 'Produção atualizada com sucesso!' : 'Produção cadastrada com sucesso!';
                if (!$editando) {
                    // Limpar formulário após cadastro
                    $producao = null;
                }
            } else {
                $erro = 'Erro ao salvar produção. Tente novamente.';
            }
        }
    } catch (Exception $e) {
        $erro = 'Erro: ' . $e->getMessage();
    }
}

$pageTitle = $editando ? 'Editar Produção' : 'Cadastrar Produção';
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
                    <i class="fas fa-list"></i> Listar Produções
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">
                            <i class="fas fa-<?php echo $editando ? 'edit' : 'plus'; ?>"></i>
                            <?php echo $pageTitle; ?>
                        </h4>
                    </div>
                    <div class="card-body">
                        <?php if (empty($lotes)): ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                Nenhum lote com estoque disponível encontrado. 
                                <a href="../lotes/cadastrar.php">Cadastre um lote</a> primeiro.
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
                                    <label for="lote_id" class="form-label">
                                        Lote <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" id="lote_id" name="lote_id" required>
                                        <option value="">Selecione o lote...</option>
                                        <?php foreach ($lotes as $lote): ?>
                                            <option value="<?php echo $lote->getId(); ?>"
                                                    data-produto="<?php echo htmlspecialchars($lote->produtoNome ?? ''); ?>"
                                                    data-estoque="<?php echo $lote->getQuantidadeRestante(); ?>"
                                                    data-custo="<?php echo $lote->getCustoPorUnidade(); ?>"
                                                    data-unidade="<?php 
                                                        $produto = $lote->getProduto();
                                                        echo $produto ? htmlspecialchars($produto->getUnidadeMedida()) : '';
                                                    ?>"
                                                    <?php echo ($producao && $producao->getLoteId() == $lote->getId()) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($lote->produtoNome ?? 'Produto'); ?> - 
                                                Lote #<?php echo $lote->getId(); ?> 
                                                (Estoque: <?php echo formatNumber($lote->getQuantidadeRestante()); ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="invalid-feedback">
                                        Por favor, selecione um lote.
                                    </div>
                                </div>

                                <!-- Informações do lote selecionado -->
                                <div id="info_lote" class="card bg-light mb-3" style="display: none;">
                                    <div class="card-body">
                                        <h6 class="card-title">
                                            <i class="fas fa-info-circle"></i>
                                            Informações do Lote
                                        </h6>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <p class="mb-1">
                                                    <strong>Produto:</strong>
                                                    <span id="lote_produto">-</span>
                                                </p>
                                            </div>
                                            <div class="col-md-4">
                                                <p class="mb-1">
                                                    <strong>Estoque Disponível:</strong>
                                                    <span id="lote_estoque" class="text-success">-</span>
                                                </p>
                                            </div>
                                            <div class="col-md-4">
                                                <p class="mb-1">
                                                    <strong>Custo por Unidade:</strong>
                                                    <span id="lote_custo" class="text-primary">-</span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="quantidade_materia_prima" class="form-label">
                                                Quantidade de Matéria-Prima Usada <span class="text-danger">*</span>
                                                <span id="unidade_materia_prima" class="text-muted"></span>
                                            </label>
                                            <input type="text" 
                                                   class="form-control" 
                                                   id="quantidade_materia_prima" 
                                                   name="quantidade_materia_prima" 
                                                   value="<?php echo $producao ? convertFromDecimal($producao->getQuantidadeMateriaPrimaUsada()) : ''; ?>"
                                                   placeholder="0,000"
                                                   required>
                                            <div class="invalid-feedback">
                                                Por favor, informe a quantidade de matéria-prima usada.
                                            </div>
                                            <div class="form-text">
                                                Quantidade do lote que será consumida nesta produção
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="quantidade_produzida" class="form-label">
                                                Quantidade de Porções Produzidas <span class="text-danger">*</span>
                                            </label>
                                            <input type="number" 
                                                   class="form-control" 
                                                   id="quantidade_produzida" 
                                                   name="quantidade_produzida" 
                                                   value="<?php echo $producao ? $producao->getQuantidadeProduzida() : ''; ?>"
                                                   min="1"
                                                   required>
                                            <div class="invalid-feedback">
                                                Por favor, informe a quantidade de porções produzidas.
                                            </div>
                                            <div class="form-text">
                                                Número de porções que serão produzidas
                                            </div>
                                        </div>
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
                                              maxlength="1000"
                                              placeholder="Observações sobre esta produção..."><?php echo $producao ? htmlspecialchars($producao->getObservacoes()) : ''; ?></textarea>
                                    <div class="form-text">
                                        Máximo 1000 caracteres
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
                                            <div class="col-md-4">
                                                <p class="mb-1">
                                                    <strong>Custo Total da Produção:</strong>
                                                    <span id="custo_total" class="text-primary">R$ 0,00</span>
                                                </p>
                                            </div>
                                            <div class="col-md-4">
                                                <p class="mb-1">
                                                    <strong>Custo por Porção:</strong>
                                                    <span id="custo_porcao" class="text-success">R$ 0,00</span>
                                                </p>
                                            </div>
                                            <div class="col-md-4">
                                                <p class="mb-1">
                                                    <strong>Rendimento:</strong>
                                                    <span id="rendimento" class="text-info">0 porções/unidade</span>
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

                <?php if ($editando && $producao): ?>
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-info-circle"></i>
                                Informações da Produção
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Data de Produção:</strong> 
                                       <?php echo formatDate($producao->getDataProducao()); ?></p>
                                    <p><strong>Estoque Disponível:</strong> 
                                       <?php echo $producao->getEstoqueDisponivel(); ?> porções</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Total de Retiradas:</strong> 
                                       <?php echo $producao->getTotalRetiradas(); ?></p>
                                    <p><strong>Quantidade Retirada:</strong> 
                                       <?php echo $producao->getQuantidadeTotalRetirada(); ?> porções</p>
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

        // Aplicar formatação
        document.getElementById('quantidade_materia_prima').addEventListener('blur', function() {
            formatarNumero(this);
            calcularCustos();
        });

        document.getElementById('quantidade_produzida').addEventListener('input', calcularCustos);

        // Atualizar informações do lote
        document.getElementById('lote_id').addEventListener('change', function() {
            const option = this.options[this.selectedIndex];
            const infoDiv = document.getElementById('info_lote');
            
            if (this.value) {
                const produto = option.getAttribute('data-produto');
                const estoque = parseFloat(option.getAttribute('data-estoque'));
                const custo = parseFloat(option.getAttribute('data-custo'));
                const unidade = option.getAttribute('data-unidade');
                
                document.getElementById('lote_produto').textContent = produto;
                document.getElementById('lote_estoque').textContent = 
                    estoque.toFixed(3).replace('.', ',') + ' ' + unidade;
                document.getElementById('lote_custo').textContent = 
                    'R$ ' + custo.toFixed(4).replace('.', ',');
                document.getElementById('unidade_materia_prima').textContent = 
                    unidade ? `(${unidade})` : '';
                
                infoDiv.style.display = 'block';
            } else {
                infoDiv.style.display = 'none';
                document.getElementById('unidade_materia_prima').textContent = '';
            }
            
            calcularCustos();
        });

        // Calcular custos automaticamente
        function calcularCustos() {
            const loteSelect = document.getElementById('lote_id');
            const quantidadeMateria = parseFloat(document.getElementById('quantidade_materia_prima').value.replace(',', '.')) || 0;
            const quantidadeProduzida = parseInt(document.getElementById('quantidade_produzida').value) || 0;
            
            let custoTotal = 0;
            let custoPorcao = 0;
            let rendimento = 0;
            
            if (loteSelect.value && quantidadeMateria > 0 && quantidadeProduzida > 0) {
                const option = loteSelect.options[loteSelect.selectedIndex];
                const custoUnidade = parseFloat(option.getAttribute('data-custo')) || 0;
                
                custoTotal = quantidadeMateria * custoUnidade;
                custoPorcao = custoTotal / quantidadeProduzida;
                rendimento = quantidadeProduzida / quantidadeMateria;
            }
            
            document.getElementById('custo_total').textContent = 
                'R$ ' + custoTotal.toFixed(2).replace('.', ',');
            document.getElementById('custo_porcao').textContent = 
                'R$ ' + custoPorcao.toFixed(4).replace('.', ',');
            document.getElementById('rendimento').textContent = 
                rendimento.toFixed(2).replace('.', ',') + ' porções/unidade';
        }

        // Inicializar na carga da página
        document.addEventListener('DOMContentLoaded', function() {
            const loteSelect = document.getElementById('lote_id');
            if (loteSelect.value) {
                loteSelect.dispatchEvent(new Event('change'));
            }
            calcularCustos();
        });
    </script>
</body>
</html>

