<?php
/**
 * Página de Listagem de Produtos
 * Sistema de Controle de Estoque - Pizzaria
 */

require_once '../../config/config.php';

$erro = '';
$sucesso = '';

// Verificar mensagens da URL
if (isset($_GET['erro'])) {
    $erro = sanitizeInput($_GET['erro']);
}
if (isset($_GET['sucesso'])) {
    $sucesso = sanitizeInput($_GET['sucesso']);
}

// Processar exclusão
if (isset($_POST['excluir']) && isset($_POST['id'])) {
    try {
        $produto = Produto::buscarPorId($_POST['id']);
        if ($produto) {
            if ($produto->podeSerExcluido()) {
                if ($produto->excluir()) {
                    $sucesso = 'Produto excluído com sucesso!';
                } else {
                    $erro = 'Erro ao excluir produto.';
                }
            } else {
                $erro = 'Produto não pode ser excluído pois possui lotes cadastrados.';
            }
        } else {
            $erro = 'Produto não encontrado.';
        }
    } catch (Exception $e) {
        $erro = 'Erro: ' . $e->getMessage();
    }
}

// Buscar produtos
$busca = sanitizeInput($_GET['busca'] ?? '');
try {
    if ($busca) {
        $produtos = Produto::buscarPorNome($busca);
    } else {
        $produtos = Produto::listarTodos();
    }
} catch (Exception $e) {
    $erro = 'Erro ao carregar produtos: ' . $e->getMessage();
    $produtos = [];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produtos - <?php echo APP_NAME; ?></title>
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
                <a class="nav-link" href="cadastrar.php">
                    <i class="fas fa-plus"></i> Novo Produto
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>
                <i class="fas fa-list"></i>
                Produtos Cadastrados
            </h2>
            <a href="cadastrar.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Novo Produto
            </a>
        </div>

        <?php if ($erro): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-triangle"></i>
                <?php echo $erro; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($sucesso): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle"></i>
                <?php echo $sucesso; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Formulário de busca -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-10">
                        <input type="text" 
                               class="form-control" 
                               name="busca" 
                               placeholder="Buscar por nome do produto..."
                               value="<?php echo htmlspecialchars($busca); ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-outline-primary w-100">
                            <i class="fas fa-search"></i> Buscar
                        </button>
                    </div>
                </form>
                <?php if ($busca): ?>
                    <div class="mt-2">
                        <a href="listar.php" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-times"></i> Limpar busca
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Tabela de produtos -->
        <div class="card">
            <div class="card-body">
                <?php if (empty($produtos)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">
                            <?php echo $busca ? 'Nenhum produto encontrado' : 'Nenhum produto cadastrado'; ?>
                        </h5>
                        <?php if (!$busca): ?>
                            <a href="cadastrar.php" class="btn btn-primary mt-3">
                                <i class="fas fa-plus"></i> Cadastrar Primeiro Produto
                            </a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Nome</th>
                                    <th>Unidade</th>
                                    <th>Data Cadastro</th>
                                    <th>Lotes</th>
                                    <th>Estoque Total</th>
                                    <th>Status</th>
                                    <th width="200">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($produtos as $produto): ?>
                                    <tr>
                                        <td><?php echo $produto->getId(); ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($produto->getNome()); ?></strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">
                                                <?php echo htmlspecialchars($produto->getUnidadeMedida()); ?>
                                            </span>
                                        </td>
                                        <td><?php echo formatDate($produto->getDataCadastro()); ?></td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                <?php echo $produto->getTotalLotes(); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php 
                                            $estoque = $produto->getEstoqueTotal();
                                            $corEstoque = $estoque > 0 ? 'success' : 'warning';
                                            ?>
                                            <span class="badge bg-<?php echo $corEstoque; ?>">
                                                <?php echo formatNumber($estoque); ?>
                                                <?php echo htmlspecialchars($produto->getUnidadeMedida()); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo $produto->isAtivo() ? 'success' : 'secondary'; ?>">
                                                <?php echo $produto->isAtivo() ? 'Ativo' : 'Inativo'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="cadastrar.php?id=<?php echo $produto->getId(); ?>" 
                                                   class="btn btn-outline-primary" 
                                                   title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="../lotes/listar.php?produto_id=<?php echo $produto->getId(); ?>" 
                                                   class="btn btn-outline-info" 
                                                   title="Ver Lotes">
                                                    <i class="fas fa-boxes"></i>
                                                </a>
                                                <?php if ($produto->podeSerExcluido()): ?>
                                                    <button type="button" 
                                                            class="btn btn-outline-danger" 
                                                            title="Excluir"
                                                            onclick="confirmarExclusao(<?php echo $produto->getId(); ?>, '<?php echo addslashes($produto->getNome()); ?>')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <button type="button" 
                                                            class="btn btn-outline-secondary" 
                                                            title="Não pode ser excluído (possui lotes)"
                                                            disabled>
                                                        <i class="fas fa-lock"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        <small class="text-muted">
                            Total: <?php echo count($produtos); ?> produto(s)
                            <?php if ($busca): ?>
                                encontrado(s) para "<?php echo htmlspecialchars($busca); ?>"
                            <?php endif; ?>
                        </small>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal de confirmação de exclusão -->
    <div class="modal fade" id="modalExcluir" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle text-danger"></i>
                        Confirmar Exclusão
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Tem certeza que deseja excluir o produto <strong id="nomeProduto"></strong>?</p>
                    <p class="text-danger">
                        <i class="fas fa-warning"></i>
                        Esta ação não pode ser desfeita!
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="id" id="idProdutoExcluir">
                        <button type="submit" name="excluir" class="btn btn-danger">
                            <i class="fas fa-trash"></i> Excluir
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmarExclusao(id, nome) {
            document.getElementById('idProdutoExcluir').value = id;
            document.getElementById('nomeProduto').textContent = nome;
            
            const modal = new bootstrap.Modal(document.getElementById('modalExcluir'));
            modal.show();
        }

        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
</body>
</html>

