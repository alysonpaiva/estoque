<?php
/**
 * Página de Cadastro de Produtos
 * Sistema de Controle de Estoque - Pizzaria
 */

require_once '../../config/config.php';

$erro = '';
$sucesso = '';
$produto = null;

// Verificar se é edição
$editando = false;
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $editando = true;
    $produto = Produto::buscarPorId($_GET['id']);
    if (!$produto) {
        header('Location: listar.php?erro=Produto não encontrado');
        exit;
    }
}

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $nome = sanitizeInput($_POST['nome'] ?? '');
        $unidadeMedida = sanitizeInput($_POST['unidade_medida'] ?? '');
        $tipoProduto = sanitizeInput($_POST['tipo_produto'] ?? 'materia_prima');
        
        if ($editando && $produto) {
            // Atualizar produto existente
            $produto->setNome($nome);
            $produto->setUnidadeMedida($unidadeMedida);
            $produto->setTipoProduto($tipoProduto);
            
            if (isset($_POST['ativo'])) {
                $produto->ativar();
            } else {
                $produto->desativar();
            }
        } else {
            // Criar novo produto
            $produto = new Produto($nome, $unidadeMedida, $tipoProduto);
        }
        
        // Validar dados
        $erros = $produto->validar();
        if (!empty($erros)) {
            $erro = implode('<br>', $erros);
        } else {
            // Salvar produto
            if ($produto->salvar()) {
                $sucesso = $editando ? 'Produto atualizado com sucesso!' : 'Produto cadastrado com sucesso!';
                if (!$editando) {
                    // Limpar formulário após cadastro
                    $produto = null;
                }
            } else {
                $erro = 'Erro ao salvar produto. Tente novamente.';
            }
        }
    } catch (Exception $e) {
        $erro = 'Erro: ' . $e->getMessage();
    }
}

$pageTitle = $editando ? 'Editar Produto' : 'Cadastrar Produto';
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
                    <i class="fas fa-list"></i> Listar Produtos
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
                                <label for="nome" class="form-label">
                                    Nome do Produto <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="nome" 
                                       name="nome" 
                                       value="<?php echo $produto ? htmlspecialchars($produto->getNome()) : ''; ?>"
                                       maxlength="100"
                                       required>
                                <div class="invalid-feedback">
                                    Por favor, informe o nome do produto.
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="unidade_medida" class="form-label">
                                    Unidade de Medida <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="unidade_medida" name="unidade_medida" required>
                                    <option value="">Selecione...</option>
                                    <?php
                                    $unidades = ['kg', 'g', 'l', 'ml', 'unidade', 'caixa', 'pacote', 'lata'];
                                    $unidadeAtual = $produto ? $produto->getUnidadeMedida() : '';
                                    foreach ($unidades as $unidade) {
                                        $selected = ($unidadeAtual === $unidade) ? 'selected' : '';
                                        echo "<option value=\"$unidade\" $selected>$unidade</option>";
                                    }
                                    ?>
                                </select>
                                <div class="invalid-feedback">
                                    Por favor, selecione a unidade de medida.
                                </div>
                                <div class="form-text">
                                    Ou digite uma unidade personalizada no campo abaixo
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="unidade_personalizada" class="form-label">
                                    Unidade Personalizada
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="unidade_personalizada" 
                                       maxlength="20"
                                       placeholder="Ex: bandeja, saco, etc.">
                                <div class="form-text">
                                    Deixe em branco para usar a unidade selecionada acima
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="tipo_produto" class="form-label">
                                    Tipo de Produto <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="tipo_produto" name="tipo_produto" required>
                                    <?php
                                    $tipos = [
                                        'materia_prima' => 'Matéria-Prima (necessita produção)',
                                        'produto_pronto' => 'Produto Pronto (direto para estoque)'
                                    ];
                                    $tipoAtual = $produto ? $produto->getTipoProduto() : 'materia_prima';
                                    foreach ($tipos as $valor => $label) {
                                        $selected = ($tipoAtual === $valor) ? 'selected' : '';
                                        echo "<option value=\"$valor\" $selected>$label</option>";
                                    }
                                    ?>
                                </select>
                                <div class="invalid-feedback">
                                    Por favor, selecione o tipo de produto.
                                </div>
                                <div class="form-text">
                                    <strong>Matéria-Prima:</strong> Produtos que precisam ser processados (ex: farinha, açúcar)<br>
                                    <strong>Produto Pronto:</strong> Produtos prontos para uso (ex: chocolate bisnaga, refrigerante)
                                </div>
                            </div>

                            <?php if ($editando): ?>
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               id="ativo" 
                                               name="ativo"
                                               <?php echo ($produto && $produto->isAtivo()) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="ativo">
                                            Produto ativo
                                        </label>
                                    </div>
                                    <div class="form-text">
                                        Produtos inativos não aparecem na listagem de cadastro de lotes
                                    </div>
                                </div>
                            <?php endif; ?>

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
                    </div>
                </div>

                <?php if ($editando && $produto): ?>
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-info-circle"></i>
                                Informações do Produto
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Data de Cadastro:</strong> 
                                       <?php echo formatDate($produto->getDataCadastro()); ?></p>
                                    <p><strong>Total de Lotes:</strong> 
                                       <?php echo $produto->getTotalLotes(); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Estoque Total:</strong> 
                                       <?php echo formatNumber($produto->getEstoqueTotal()); ?> 
                                       <?php echo $produto->getUnidadeMedida(); ?></p>
                                    <p><strong>Status:</strong> 
                                       <span class="badge bg-<?php echo $produto->isAtivo() ? 'success' : 'secondary'; ?>">
                                           <?php echo $produto->isAtivo() ? 'Ativo' : 'Inativo'; ?>
                                       </span>
                                    </p>
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

        // Gerenciar unidade de medida personalizada
        document.getElementById('unidade_personalizada').addEventListener('input', function() {
            const select = document.getElementById('unidade_medida');
            const input = this;
            
            if (input.value.trim() !== '') {
                select.value = '';
                select.removeAttribute('required');
                // Criar opção temporária com valor personalizado
                let option = select.querySelector('option[data-custom]');
                if (option) {
                    option.remove();
                }
                option = document.createElement('option');
                option.value = input.value.trim();
                option.textContent = input.value.trim();
                option.selected = true;
                option.setAttribute('data-custom', 'true');
                select.appendChild(option);
            } else {
                select.setAttribute('required', 'required');
                let option = select.querySelector('option[data-custom]');
                if (option) {
                    option.remove();
                }
            }
        });

        document.getElementById('unidade_medida').addEventListener('change', function() {
            if (this.value !== '') {
                document.getElementById('unidade_personalizada').value = '';
            }
        });
    </script>
</body>
</html>

