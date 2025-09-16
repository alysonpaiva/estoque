<?php
require_once 'config/config.php';

echo "<h2>DEBUG - Dados para Retirada</h2>";

echo "<h3>1. Testando Produções Disponíveis:</h3>";
try {
    $producoes = Producao::listarDisponiveis();
    echo "Quantidade de produções: " . count($producoes) . "<br>";
    
    if (empty($producoes)) {
        echo "<strong>PROBLEMA: Nenhuma produção disponível!</strong><br>";
        
        // Verificar se existem produções no banco
        $db = Database::getInstance();
        $totalProducoes = $db->query("SELECT COUNT(*) as total FROM producao")[0]['total'];
        echo "Total de produções no banco: " . $totalProducoes . "<br>";
        
        if ($totalProducoes > 0) {
            echo "<h4>Produções existentes:</h4>";
            $todasProducoes = $db->query("
                SELECT pr.*, p.nome as produto_nome,
                       (pr.quantidade_produzida - COALESCE(SUM(r.quantidade_retirada), 0)) as quantidade_disponivel
                FROM producao pr
                LEFT JOIN lotes l ON pr.lote_id = l.id
                LEFT JOIN produtos p ON l.produto_id = p.id
                LEFT JOIN retiradas r ON pr.id = r.producao_id
                GROUP BY pr.id
                ORDER BY pr.data_producao DESC
            ");
            
            foreach ($todasProducoes as $prod) {
                echo "ID: {$prod['id']}, Produto: {$prod['produto_nome']}, ";
                echo "Produzido: {$prod['quantidade_produzida']}, ";
                echo "Disponível: {$prod['quantidade_disponivel']}<br>";
            }
        }
    } else {
        echo "<h4>Produções encontradas:</h4>";
        foreach ($producoes as $prod) {
            echo "ID: {$prod->getId()}, ";
            echo "Produto: {$prod->getProdutoNome()}, ";
            echo "Lote: {$prod->getLoteId()}, ";
            echo "Disponível: {$prod->getQuantidadeDisponivel()}<br>";
        }
    }
} catch (Exception $e) {
    echo "<strong>ERRO: " . $e->getMessage() . "</strong><br>";
}

echo "<h3>2. Testando Produtos Prontos:</h3>";
try {
    $db = Database::getInstance();
    $produtosProntos = $db->fetchAll("
        SELECT p.id, p.nome, p.unidade_medida,
               COALESCE(SUM(ed.quantidade_entrada), 0) - COALESCE(SUM(rd.quantidade_retirada), 0) as estoque_disponivel
        FROM produtos p
        LEFT JOIN entradas_diretas ed ON p.id = ed.produto_id
        LEFT JOIN retiradas_diretas rd ON p.id = rd.produto_id
        WHERE p.tipo_produto = 'produto_pronto' AND p.ativo = 1
        GROUP BY p.id, p.nome, p.unidade_medida
        HAVING estoque_disponivel > 0
        ORDER BY p.nome
    ");
    
    echo "Quantidade de produtos prontos: " . count($produtosProntos) . "<br>";
    
    if (empty($produtosProntos)) {
        echo "<strong>PROBLEMA: Nenhum produto pronto disponível!</strong><br>";
        
        // Verificar produtos prontos cadastrados
        $totalProdutosProntos = $db->query("SELECT COUNT(*) as total FROM produtos WHERE tipo_produto = 'produto_pronto'")[0]['total'];
        echo "Total de produtos prontos cadastrados: " . $totalProdutosProntos . "<br>";
        
        if ($totalProdutosProntos > 0) {
            echo "<h4>Produtos prontos cadastrados:</h4>";
            $todosProdutosProntos = $db->query("SELECT * FROM produtos WHERE tipo_produto = 'produto_pronto'");
            foreach ($todosProdutosProntos as $prod) {
                echo "ID: {$prod['id']}, Nome: {$prod['nome']}, Ativo: {$prod['ativo']}<br>";
            }
        }
    } else {
        echo "<h4>Produtos prontos encontrados:</h4>";
        foreach ($produtosProntos as $prod) {
            echo "ID: {$prod['id']}, Nome: {$prod['nome']}, Estoque: {$prod['estoque_disponivel']}<br>";
        }
    }
} catch (Exception $e) {
    echo "<strong>ERRO: " . $e->getMessage() . "</strong><br>";
}

echo "<h3>3. Verificando Estrutura do Banco:</h3>";
try {
    $db = Database::getInstance();
    
    // Verificar se tabelas existem
    $tabelas = ['produtos', 'lotes', 'producao', 'retiradas', 'entradas_diretas', 'retiradas_diretas'];
    foreach ($tabelas as $tabela) {
        try {
            $count = $db->query("SELECT COUNT(*) as total FROM $tabela")[0]['total'];
            echo "Tabela $tabela: $count registros<br>";
        } catch (Exception $e) {
            echo "Tabela $tabela: <strong>NÃO EXISTE</strong><br>";
        }
    }
    
    // Verificar campo tipo_produto
    try {
        $produtos = $db->query("SELECT id, nome, tipo_produto FROM produtos LIMIT 5");
        echo "<h4>Primeiros 5 produtos:</h4>";
        foreach ($produtos as $prod) {
            echo "ID: {$prod['id']}, Nome: {$prod['nome']}, Tipo: " . ($prod['tipo_produto'] ?? 'NULL') . "<br>";
        }
    } catch (Exception $e) {
        echo "<strong>ERRO ao verificar produtos: " . $e->getMessage() . "</strong><br>";
    }
    
} catch (Exception $e) {
    echo "<strong>ERRO: " . $e->getMessage() . "</strong><br>";
}
?>

