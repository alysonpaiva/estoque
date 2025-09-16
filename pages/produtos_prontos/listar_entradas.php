<?php
/**
 * Página de Listagem de Entradas de Produtos Prontos
 * Sistema de Controle de Estoque - Pizzaria
 */

require_once '../../config/config.php';

$erro = '';
$sucesso = '';

// Processar ações
if (isset($_GET['acao']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $acao = $_GET['acao'];
    
    if ($acao === 'excluir') {
        try {
            $entrada = EntradaDireta::buscarPorId($id);
            if ($entrada && $entrada->excluir()) {
                $sucesso = 'Entrada excluída com sucesso!';
            } else {
                $erro = 'Erro ao excluir entrada.';
            }
        } catch (Exception $e) {
            $erro = 'Erro: ' . $e->getMessage();
        }
    }
}

// Parâmetros de busca e paginação
$busca = sanitizeInput($_GET['busca'] ?? '');
$produtoId = (int)($_GET['produto_id'] ?? 0);
$pagina = max(1, (int)($_GET['pagina'] ?? 1));
$itensPorPagina = 20;
$offset = ($pagina - 1) * $itensPorPagina;

// Buscar entradas
$entradas = [];
$totalEntradas = 0;
try {
    $db = Database::getInstance();
    
    // Query base
    $sqlBase = "FROM entradas_diretas ed
                LEFT JOIN produtos p ON ed.produto_id = p.id
                WHERE p.tipo_produto = 'produto_pronto'";
    
    $params = [];
    
    // Filtros
    if ($busca) {
        $sqlBase .= " AND (p.nome LIKE :busca OR ed.fornecedor LIKE :busca OR ed.nota_fiscal LIKE :busca)";
        $params[':busca'] = "%$busca%";
    }
    
    if ($produtoId) {
        $sqlBase .= " AND ed.produto_id = :produto_id";
        $params[':produto_id'] = $produtoId;
    }
    
    // Contar total
    $sqlCount = "SELECT COUNT(*) as total " . $sqlBase;
    $resultCount = $db->fetchOne($sqlCount, $params);
    $totalEntradas = $resultCount['total'];
    
    // Buscar entradas com paginação
    $sql = "SELECT ed.*, p.nome as produto_nome, p.unidade_medida " . $sqlBase . 
           " ORDER BY ed.data_entrada DESC LIMIT :limit OFFSET :offset";
    $params[':limit'] = $itensPorPagina;
    $params[':offset'] = $offset;
    
    $results = $db->fetchAll($sql, $params);
    
    foreach ($results as $result) {
        $entrada = new EntradaDireta();
        $entrada->setId($result['id']);
        $entrada->setProdutoId($result['produto_id']);
        $entrada->setQuantidadeEntrada($result['quantidade_entrada']);
        $entrada->setPrecoUnitario($result['preco_unitario']);
        $entrada->setFornecedor($result['fornecedor']);
        $entrada->setNotaFiscal($result['nota_fiscal']);
        $entrada->setDataEntrada($result['data_entrada']);
        $entrada->setObservacoes($result['observacoes']);
        
        // Adicionar informações do produto
        $entrada->produtoNome = $result['produto_nome'];
        $entrada->unidadeMedida = $result['unidade_medida'];
        $entrada->valorTotal = $result['valor_total'];
        
        $entradas[] = $entrada;
    }
} catch (Exception $e) {
    $erro = 'Erro ao carregar entradas: ' . $e->getMessage();
}

// Buscar produtos para filtro
$produtosProntos = [];
try {
    $sql = "SELECT id, nome FROM produtos WHERE tipo_produto = 'produto_pronto' AND ativo = 1 ORDER BY nome";
    $produtosProntos = $db->fetchAll($sql);
} catch (Exception $e) {
    // Ignorar erro do filtro
}

// Calcular paginação
$totalPaginas = ceil($totalEntradas / $itensPorPagina);

$pageTitle = 'Entradas de Produtos Prontos - ' . APP_NAME;
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
                    <li class="nav-item dropdown active">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-bag-check"></i> Produtos Prontos
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="entrada.php">Nova Entrada</a></li>
                            <li><a class="dropdown-item active" href="listar_entradas.php">Listar Entradas</a></li>
                            <li><a class="dropdown-item" href="retirada.php">Nova Retirada</a></li>
                            <li><a class="dropdown-item" href="listar_retiradas.php">Listar Retiradas</a></li>
                        </ul>
                    </li>
                </ul>
                <a href="entrada.php" class="btn btn-light">
                    <i class="bi bi-plus-circle"></i> Nova Entrada
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../../pages/relatorios/dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Entradas de Produtos Prontos</li>
            </ol>
        </nav>

        <!-- Título e ações -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3">
                <i class="bi bi-list-ul"></i>
                Entradas de Produtos Prontos
            </h1>
            <a href="entrada.php" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Nova Entrada
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

        <!-- Filtros -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label for="busca" class="form-label">Buscar</label>
                        <input type="text" 
                               class="form-control" 
                               id="busca" 
                               name="busca" 
                               value="<?= htmlspecialchars($busca) ?>"
                               placeholder="Produto, fornecedor ou nota fiscal...">
                    </div>
                    <div class="col-md-4">
                        <label for="produto_id" class="form-label">Produto</label>
                        <select class="form-select" id="produto_id" name="produto_id">
                            <option value="">Todos os produtos</option>
                            <?php foreach ($produtosProntos as $produto): ?>
                                <?php $selected = ($produtoId == $produto['id']) ? 'selected' : ''; ?>
                                <option value="<?= $produto['id'] ?>" <?= $selected ?>>
                                    <?= htmlspecialchars($produto['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="bi bi-search"></i> Buscar
                        </button>
                        <a href="listar_entradas.php" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle"></i> Limpar
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Estatísticas -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title text-primary"><?= $totalEntradas ?></h5>
                        <p class="card-text">Total de Entradas</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <?php
                        $valorTotalEntradas = 0;
                        foreach ($entradas as $entrada) {
                            $valorTotalEntradas += $entrada->valorTotal;
                        }
                        ?>
                        <h5 class="card-title text-success"><?= formatMoney($valorTotalEntradas) ?></h5>
                        <p class="card-text">Valor Total (Página)</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabela -->
        <div class="card">
            <div class="card-body">
                <?php if (empty($entradas)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-inbox display-1 text-muted"></i>
                        <h4 class="text-muted mt-3">Nenhuma entrada encontrada</h4>
                        <p class="text-muted">
                            <?php if ($busca || $produtoId): ?>
                                Tente ajustar os filtros de busca.
                            <?php else: ?>
                                Comece registrando uma nova entrada de produto pronto.
                            <?php endif; ?>
                        </p>
                        <a href="entrada.php" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Nova Entrada
                        </a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Data</th>
                                    <th>Produto</th>
                                    <th>Quantidade</th>
                                    <th>Preço Unit.</th>
                                    <th>Valor Total</th>
                                    <th>Fornecedor</th>
                                    <th>Nota Fiscal</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($entradas as $entrada): ?>
                                    <tr>
                                        <td>
                                            <small class="text-muted">
                                                <?= $entrada->getDataEntrada()->format('d/m/Y') ?><br>
                                                <?= $entrada->getDataEntrada()->format('H:i') ?>
                                            </small>
                                        </td>
                                        <td>
                                            <strong><?= htmlspecialchars($entrada->produtoNome) ?></strong><br>
                                            <small class="text-muted"><?= $entrada->unidadeMedida ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">
                                                <?= number_format($entrada->getQuantidadeEntrada(), 3, ',', '.') ?>
                                            </span>
                                        </td>
                                        <td><?= formatMoney($entrada->getPrecoUnitario()) ?></td>
                                        <td>
                                            <strong class="text-success">
                                                <?= formatMoney($entrada->valorTotal) ?>
                                            </strong>
                                        </td>
                                        <td>
                                            <?php if ($entrada->getFornecedor()): ?>
                                                <?= htmlspecialchars($entrada->getFornecedor()) ?>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($entrada->getNotaFiscal()): ?>
                                                <code><?= htmlspecialchars($entrada->getNotaFiscal()) ?></code>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="entrada.php?id=<?= $entrada->getId() ?>" 
                                                   class="btn btn-outline-primary" 
                                                   title="Editar">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <button type="button" 
                                                        class="btn btn-outline-danger" 
                                                        onclick="confirmarExclusao(<?= $entrada->getId() ?>)"
                                                        title="Excluir">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginação -->
                    <?php if ($totalPaginas > 1): ?>
                        <nav aria-label="Paginação">
                            <ul class="pagination justify-content-center">
                                <?php if ($pagina > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?pagina=<?= $pagina - 1 ?>&busca=<?= urlencode($busca) ?>&produto_id=<?= $produtoId ?>">
                                            Anterior
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <?php for ($i = max(1, $pagina - 2); $i <= min($totalPaginas, $pagina + 2); $i++): ?>
                                    <li class="page-item <?= $i == $pagina ? 'active' : '' ?>">
                                        <a class="page-link" href="?pagina=<?= $i ?>&busca=<?= urlencode($busca) ?>&produto_id=<?= $produtoId ?>">
                                            <?= $i ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($pagina < $totalPaginas): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?pagina=<?= $pagina + 1 ?>&busca=<?= urlencode($busca) ?>&produto_id=<?= $produtoId ?>">
                                            Próxima
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal de confirmação -->
    <div class="modal fade" id="modalExcluir" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar Exclusão</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Tem certeza que deseja excluir esta entrada?</p>
                    <p class="text-danger">
                        <i class="bi bi-exclamation-triangle"></i>
                        Esta ação não pode ser desfeita.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <a href="#" id="linkExcluir" class="btn btn-danger">Excluir</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmarExclusao(id) {
            document.getElementById('linkExcluir').href = '?acao=excluir&id=' + id;
            new bootstrap.Modal(document.getElementById('modalExcluir')).show();
        }
    </script>
</body>
</html>

