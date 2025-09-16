<?php
/**
 * Script para atualizar menus de todas as páginas
 */

$menuCompleto = '            <div class="collapse navbar-collapse" id="navbarNav">
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
                            <i class="bi bi-truck"></i> Retiradas
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="../retiradas/cadastrar.php">Nova Retirada</a></li>
                            <li><a class="dropdown-item" href="../retiradas/listar.php">Listar Retiradas</a></li>
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
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-currency-dollar"></i> Vendas
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="../vendas/cadastrar.php">Nova Venda</a></li>
                            <li><a class="dropdown-item" href="../vendas/listar.php">Listar Vendas</a></li>
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
            </div>';

// Arquivos para atualizar
$arquivos = [
    'pages/produtos/cadastrar.php',
    'pages/produtos/listar.php',
    'pages/lotes/cadastrar.php',
    'pages/lotes/listar.php',
    'pages/producao/cadastrar.php',
    'pages/producao/listar.php',
    'pages/retiradas/cadastrar.php',
    'pages/retiradas/listar.php',
    'pages/relatorios/dashboard.php',
    'pages/relatorios/entradas.php',
    'pages/relatorios/producao.php',
    'pages/relatorios/retiradas.php',
    'pages/produtos_prontos/entrada.php',
    'pages/produtos_prontos/listar_entradas.php',
    'pages/produtos_prontos/retirada.php'
];

foreach ($arquivos as $arquivo) {
    if (file_exists($arquivo)) {
        $conteudo = file_get_contents($arquivo);
        
        // Encontrar e substituir o menu
        $pattern = '/(<div class="collapse navbar-collapse" id="navbarNav">.*?<\/div>)/s';
        $conteudo = preg_replace($pattern, $menuCompleto, $conteudo);
        
        file_put_contents($arquivo, $conteudo);
        echo "✅ Atualizado: $arquivo\n";
    } else {
        echo "❌ Não encontrado: $arquivo\n";
    }
}

echo "\n🎉 Todos os menus foram atualizados!\n";
?>

