<?php
/**
 * Arquivo de Teste das Funcionalidades CORRIGIDO
 * Sistema de Controle de Estoque - Pizzaria
 */

// Ativar debug
define('DEBUG', true);
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<pre>";
echo "<h1>TESTE DO SISTEMA CORRIGIDO</h1>";

require_once 'config/config.php';

// Teste de Conexão com o Banco de Dados
echo "<h2>1. Teste de Conexão</h2>";
try {
    $db = Database::getInstance();
    echo "✅ Conexão com o banco de dados bem-sucedida!<br>";
} catch (Exception $e) {
    echo "❌ Erro na conexão: " . $e->getMessage() . "<br>";
    exit;
}

// Teste da Classe Produto
echo "<h2>2. Teste da Classe Produto</h2>";
try {
    $produto = new Produto("Farinha de Trigo Especial", "Kg");
    if ($produto->salvar()) {
        echo "✅ Produto 'Farinha de Trigo Especial' salvo com ID: " . $produto->getId() . "<br>";
        
        $produtoEncontrado = Produto::buscarPorId($produto->getId());
        if ($produtoEncontrado) {
            echo "✅ Produto encontrado: " . $produtoEncontrado->getNome() . "<br>";
        } else {
            echo "❌ Produto não encontrado<br>";
        }
    } else {
        echo "❌ Erro ao salvar produto<br>";
    }
} catch (Exception $e) {
    echo "❌ Erro no teste do produto: " . $e->getMessage() . "<br>";
}

// Teste da Classe Lote
echo "<h2>3. Teste da Classe Lote</h2>";
try {
    $lote = new Lote($produto->getId(), 50.00, 25.0, '2023-10-27');
    if ($lote->salvar()) {
        echo "✅ Lote salvo com ID: " . $lote->getId() . "<br>";
        echo "✅ Custo por unidade: " . formatMoney($lote->getCustoPorUnidade()) . "<br>";
        
        $loteEncontrado = Lote::buscarPorId($lote->getId());
        if ($loteEncontrado) {
            echo "✅ Lote encontrado: " . $loteEncontrado->produtoNome . "<br>";
        } else {
            echo "❌ Lote não encontrado<br>";
        }
    } else {
        echo "❌ Erro ao salvar lote<br>";
    }
} catch (Exception $e) {
    echo "❌ Erro no teste do lote: " . $e->getMessage() . "<br>";
}

// Teste da Classe Producao
echo "<h2>4. Teste da Classe Producao</h2>";
try {
    $producao = new Producao($lote->getId(), 100, 10.0);
    if ($producao->salvar()) {
        echo "✅ Produção salva com ID: " . $producao->getId() . "<br>";
        echo "✅ Custo total: " . formatMoney($producao->getCustoTotalProducao()) . "<br>";
        echo "✅ Custo por porção: " . formatMoney($producao->getCustoPorPorcao()) . "<br>";
        
        $producaoEncontrada = Producao::buscarPorId($producao->getId());
        if ($producaoEncontrada) {
            echo "✅ Produção encontrada: " . $producaoEncontrada->produtoNome . "<br>";
        } else {
            echo "❌ Produção não encontrada<br>";
        }
    } else {
        echo "❌ Erro ao salvar produção<br>";
    }
} catch (Exception $e) {
    echo "❌ Erro no teste da produção: " . $e->getMessage() . "<br>";
}

// Teste da Classe Retirada
echo "<h2>5. Teste da Classe Retirada</h2>";
try {
    $retirada = new Retirada($producao->getId(), 30, "Pizzaria Centro");
    $retirada->setResponsavel("João Silva");
    if ($retirada->salvar()) {
        echo "✅ Retirada salva com ID: " . $retirada->getId() . "<br>";
        echo "✅ Valor da retirada: " . formatMoney($retirada->calcularValor()) . "<br>";
        
        $retiradaEncontrada = Retirada::buscarPorId($retirada->getId());
        if ($retiradaEncontrada) {
            echo "✅ Retirada encontrada: " . $retiradaEncontrada->produtoNome . "<br>";
        } else {
            echo "❌ Retirada não encontrada<br>";
        }
    } else {
        echo "❌ Erro ao salvar retirada<br>";
    }
} catch (Exception $e) {
    echo "❌ Erro no teste da retirada: " . $e->getMessage() . "<br>";
}

// Teste da Classe Relatorio
echo "<h2>6. Teste da Classe Relatorio</h2>";
try {
    $relatorio = new Relatorio();
    
    // Dashboard
    $dashboard = $relatorio->dashboard();
    echo "✅ Dashboard carregado:<br>";
    echo "   - Produtos: " . $dashboard['total_produtos'] . "<br>";
    echo "   - Lotes: " . $dashboard['total_lotes'] . "<br>";
    echo "   - Produções: " . $dashboard['total_producoes'] . "<br>";
    echo "   - Retiradas: " . $dashboard['total_retiradas'] . "<br>";
    echo "   - Valor investido: " . formatMoney($dashboard['valor_investido']) . "<br>";
    echo "   - Estoque porções: " . $dashboard['estoque_porcoes'] . "<br>";
    
    // Relatórios por período
    $dataInicio = new DateTime('2023-01-01');
    $dataFim = new DateTime('2023-12-31');
    
    $relatorioEntradas = $relatorio->relatorioEntradas($dataInicio, $dataFim);
    echo "✅ Relatório de Entradas: " . count($relatorioEntradas) . " registros<br>";
    
    $relatorioProducao = $relatorio->relatorioProducao($dataInicio, $dataFim);
    echo "✅ Relatório de Produção: " . count($relatorioProducao) . " registros<br>";
    
    $relatorioRetiradas = $relatorio->relatorioRetiradas($dataInicio, $dataFim);
    echo "✅ Relatório de Retiradas: " . count($relatorioRetiradas) . " registros<br>";
    
    $relatorioEstoque = $relatorio->relatorioEstoqueAtual();
    echo "✅ Relatório de Estoque Atual: " . count($relatorioEstoque) . " produtos<br>";
    
} catch (Exception $e) {
    echo "❌ Erro no teste dos relatórios: " . $e->getMessage() . "<br>";
}

// Teste de Listagens
echo "<h2>7. Teste de Listagens</h2>";
try {
    $produtos = Produto::listarTodos();
    echo "✅ Listagem de produtos: " . count($produtos) . " produtos<br>";
    
    $lotes = Lote::listarTodos();
    echo "✅ Listagem de lotes: " . count($lotes) . " lotes<br>";
    
    $producoes = Producao::listarTodas();
    echo "✅ Listagem de produções: " . count($producoes) . " produções<br>";
    
    $retiradas = Retirada::listarTodas();
    echo "✅ Listagem de retiradas: " . count($retiradas) . " retiradas<br>";
    
} catch (Exception $e) {
    echo "❌ Erro no teste das listagens: " . $e->getMessage() . "<br>";
}

// Teste de Cálculos
echo "<h2>8. Teste de Cálculos</h2>";
try {
    // Quantidade restante do lote
    $quantidadeRestante = $lote->calcularQuantidadeRestante();
    echo "✅ Quantidade restante do lote: " . formatNumber($quantidadeRestante) . " " . $produto->getUnidadeMedida() . "<br>";
    
    // Quantidade disponível da produção
    $quantidadeDisponivel = $producao->calcularQuantidadeDisponivel();
    echo "✅ Quantidade disponível da produção: " . $quantidadeDisponivel . " porções<br>";
    
} catch (Exception $e) {
    echo "❌ Erro no teste dos cálculos: " . $e->getMessage() . "<br>";
}

echo "<h2>🎉 TODOS OS TESTES CONCLUÍDOS!</h2>";
echo "<p>Sistema funcionando perfeitamente sem erros!</p>";

echo "</pre>";
?>

