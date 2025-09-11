<?php
/**
 * Página de Cadastro de Retiradas
 * Sistema de Controle de Estoque - Pizzaria
 */

require_once '../../config/config.php';

$erro = '';
$sucesso = '';
$retirada = null;

// Verificar se é edição
$editando = false;
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $editando = true;
    $retirada = Retirada::buscarPorId($_GET['id']);
    if (!$retirada) {
        header('Location: listar.php?erro=Retirada não encontrada');
        exit;
    }
}

// Carregar produções disponíveis
try {
    $producoes = Producao::listarDisponiveis();
} catch (Exception $e) {
    $erro = 'Erro ao carregar produções: ' . $e->getMessage();
    $producoes = [];
}

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $producaoId = (int) ($_POST['producao_id'] ?? 0);
        $quantidadeRetirada = (int) ($_POST['quantidade_retirada'] ?? 0);
        $destino = sanitizeInput($_POST['destino'] ?? '');
        $responsavel = sanitizeInput($_POST['responsavel'] ?? '');
        $observacoes = sanitizeInput($_POST['observacoes'] ?? '');
        
        if ($editando && $retirada) {
            // Atualizar retirada existente
            $retirada->setProducaoId($producaoId);
            $retirada->setQuantidadeRetirada($quantidadeRetirada);
            $retirada->setDestino($destino);
            $retirada->setResponsavel($responsavel);
            $retirada->setObservacoes($observacoes);
        } else {
            // Criar nova retirada
            $retirada = new Retirada($producaoId, $quantidadeRetirada, $destino);
            $retirada->setResponsavel($responsavel);
            $retirada->setObservacoes($observacoes);
        }
        
        // Validar dados
        $erros = $retirada->validar();
        if (!empty($erros)) {
            $erro = implode('<br>', $erros);
        } else {
            // Salvar retirada
            if ($retirada->salvar()) {
                $sucesso = $editando ? 'Retirada atualizada com sucesso!' : 'Retirada cadastrada com sucesso!';
                if (!$editando) {
                    // Limpar formulário após cadastro
                    $retirada = null;
                }
            } else {
                $erro = 'Erro ao salvar retirada. Tente novamente.';
            }
        }
    } catch (Exception $e) {
        $erro = 'Erro: ' . $e->getMessage();
    }
}

$pageTitle = $editando ? 'Editar Retirada' : 'Cadastrar Retirada';
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
                    <i class="fas fa-list"></i> Listar Retiradas
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
                            <i class="fas fa-<?php echo $editando ? 'edit' : 'truck'; ?>"></i>
                            <?php echo $pageTitle; ?>
                        </h4>
                    </div>
                    <div class="card-body">
                        <?php if (empty($producoes)): ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                Nenhuma produção com estoque disponível encontrada. 
                                <a href="../producao/cadastrar.php">Cadastre uma produção</a> primeiro.
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
                                    <label for="producao_id" class="form-label">
                                        Produção <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" id="producao_id" name="producao_id" required>
                                        <option value="">Selecione a produção...</option>
                                        <?php foreach ($producoes as $producao): ?>
                                            <option value="<?php echo $producao->getId(); ?>"
                                                    data-produto="<?php echo htmlspecialchars($producao->produtoNome ?? ''); ?>"
                                                    data-estoque="<?php echo $producao->quantidadeDisponivel; ?>"
                                                    data-custo="<?php echo $producao->getCustoPorPorcao(); ?>"
                                                    data-data="<?php echo formatDate($producao->getDataProducao()); ?>"
                                                    <?php echo ($retirada && $retirada->getProducaoId() == $producao->getId()) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($producao->produtoNome ?? 'Produto'); ?> - 
                                                Produção #<?php echo $producao->getId(); ?> 
                                                (<?php echo formatDate($producao->getDataProducao()); ?>) - 
                                                Estoque: <?php echo $producao->quantidadeDisponivel; ?> porções
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="invalid-feedback">
                                        Por favor, selecione uma produção.
                                    </div>
                                </div>

                                <!-- Informações da produção selecionada -->
                                <div id="info_producao" class="card bg-light mb-3" style="display: none;">
                                    <div class="card-body">
                                        <h6 class="card-title">
                                            <i class="fas fa-info-circle"></i>
                                            Informações da Produção
                                        </h6>
                                        <div class="row">
                                            <div class="col-md-3">
                                                <p class="mb-1">
                                                    <strong>Produto:</strong>
                                                    <span id="producao_produto">-</span>
                                                </p>
                                            </div>
                                            <div class="col-md-3">
                                                <p class="mb-1">
                                                    <strong>Data Produção:</strong>
                                                    <span id="producao_data">-</span>
                                                </p>
                                            </div>
                                            <div class="col-md-3">
                                                <p class="mb-1">
                                                    <strong>Estoque Disponível:</strong>
                                                    <span id="producao_estoque" class="text-success">-</span>
                                                </p>
                                            </div>
                                            <div class="col-md-3">
                                                <p class="mb-1">
                                                    <strong>Custo por Porção:</strong>
                                                    <span id="producao_custo" class="text-primary">-</span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="quantidade_retirada" class="form-label">
                                                Quantidade a Retirar <span class="text-danger">*</span>
                                            </label>
                                            <div class="input-group">
                                                <input type="number" 
                                                       class="form-control" 
                                                       id="quantidade_retirada" 
                                                       name="quantidade_retirada" 
                                                       value="<?php echo $retirada ? $retirada->getQuantidadeRetirada() : ''; ?>"
                                                       min="1"
                                                       required>
                                                <span class="input-group-text">porções</span>
                                            </div>
                                            <div class="invalid-feedback">
                                                Por favor, informe a quantidade a retirar.
                                            </div>
                                            <div class="form-text">
                                                Máximo disponível: <span id="max_disponivel">0</span> porções
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="destino" class="form-label">
                                                Destino <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" 
                                                   class="form-control" 
                                                   id="destino" 
                                                   name="destino" 
                                                   value="<?php echo $retirada ? htmlspecialchars($retirada->getDestino()) : ''; ?>"
                                                   maxlength="100"
                                                   placeholder="Ex: Pizzaria Centro"
                                                   required>
                                            <div class="invalid-feedback">
                                                Por favor, informe o destino.
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="responsavel" class="form-label">
                                        Responsável
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="responsavel" 
                                           name="responsavel" 
                                           value="<?php echo $retirada ? htmlspecialchars($retirada->getResponsavel()) : ''; ?>"
                                           maxlength="100"
                                           placeholder="Nome do responsável pela retirada">
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
                                              placeholder="Observações sobre esta retirada..."><?php echo $retirada ? htmlspecialchars($retirada->getObservacoes()) : ''; ?></textarea>
                                    <div class="form-text">
                                        Máximo 1000 caracteres
                                    </div>
                                </div>

                                <!-- Cálculos automáticos -->
                                <div class="card bg-light mb-3">
                                    <div class="card-body">
                                        <h6 class="card-title">
                                            <i class="fas fa-calculator"></i>
                                            Resumo da Retirada
                                        </h6>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <p class="mb-1">
                                                    <strong>Valor Total:</strong>
                                                    <span id="valor_total" class="text-primary">R$ 0,00</span>
                                                </p>
                                            </div>
                                            <div class="col-md-4">
                                                <p class="mb-1">
                                                    <strong>Estoque Após Retirada:</strong>
                                                    <span id="estoque_apos" class="text-info">0 porções</span>
                                                </p>
                                            </div>
                                            <div class="col-md-4">
                                                <p class="mb-1">
                                                    <strong>% do Estoque:</strong>
                                                    <span id="percentual_estoque" class="text-warning">0%</span>
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
                                        <i class="fas fa-truck"></i>
                                        <?php echo $editando ? 'Atualizar' : 'Registrar Retirada'; ?>
                                    </button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if ($editando && $retirada): ?>
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-info-circle"></i>
                                Informações da Retirada
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Data da Retirada:</strong> 
                                       <?php echo formatDate($retirada->getDataRetirada(), DATETIME_FORMAT); ?></p>
                                    <p><strong>Valor Total:</strong> 
                                       <?php echo formatMoney($retirada->calcularValor()); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Produto:</strong> 
                                       <?php 
                                       $produto = $retirada->getProduto();
                                       echo $produto ? htmlspecialchars($produto->getNome()) : 'N/A';
                                       ?></p>
                                    <p><strong>Produção:</strong> 
                                       #<?php echo $retirada->getProducaoId(); ?></p>
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

        // Atualizar informações da produção
        document.getElementById('producao_id').addEventListener('change', function() {
            const option = this.options[this.selectedIndex];
            const infoDiv = document.getElementById('info_producao');
            
            if (this.value) {
                const produto = option.getAttribute('data-produto');
                const estoque = parseInt(option.getAttribute('data-estoque'));
                const custo = parseFloat(option.getAttribute('data-custo'));
                const data = option.getAttribute('data-data');
                
                document.getElementById('producao_produto').textContent = produto;
                document.getElementById('producao_data').textContent = data;
                document.getElementById('producao_estoque').textContent = estoque + ' porções';
                document.getElementById('producao_custo').textContent = 
                    'R$ ' + custo.toFixed(4).replace('.', ',');
                document.getElementById('max_disponivel').textContent = estoque;
                
                // Atualizar limite do input
                const quantidadeInput = document.getElementById('quantidade_retirada');
                quantidadeInput.setAttribute('max', estoque);
                
                infoDiv.style.display = 'block';
            } else {
                infoDiv.style.display = 'none';
                document.getElementById('max_disponivel').textContent = '0';
            }
            
            calcularResumo();
        });

        // Calcular resumo da retirada
        document.getElementById('quantidade_retirada').addEventListener('input', calcularResumo);

        function calcularResumo() {
            const producaoSelect = document.getElementById('producao_id');
            const quantidade = parseInt(document.getElementById('quantidade_retirada').value) || 0;
            
            let valorTotal = 0;
            let estoqueApos = 0;
            let percentual = 0;
            
            if (producaoSelect.value && quantidade > 0) {
                const option = producaoSelect.options[producaoSelect.selectedIndex];
                const estoque = parseInt(option.getAttribute('data-estoque'));
                const custo = parseFloat(option.getAttribute('data-custo'));
                
                valorTotal = quantidade * custo;
                estoqueApos = estoque - quantidade;
                percentual = (quantidade / estoque) * 100;
                
                // Validar se não excede o estoque
                if (quantidade > estoque) {
                    document.getElementById('quantidade_retirada').setCustomValidity(
                        'Quantidade não pode exceder o estoque disponível'
                    );
                } else {
                    document.getElementById('quantidade_retirada').setCustomValidity('');
                }
            }
            
            document.getElementById('valor_total').textContent = 
                'R$ ' + valorTotal.toFixed(2).replace('.', ',');
            document.getElementById('estoque_apos').textContent = 
                estoqueApos + ' porções';
            document.getElementById('percentual_estoque').textContent = 
                percentual.toFixed(1).replace('.', ',') + '%';
        }

        // Inicializar na carga da página
        document.addEventListener('DOMContentLoaded', function() {
            const producaoSelect = document.getElementById('producao_id');
            if (producaoSelect.value) {
                producaoSelect.dispatchEvent(new Event('change'));
            }
            calcularResumo();
        });
    </script>
</body>
</html>

