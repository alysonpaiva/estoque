<?php
/**
 * Teste de diferentes abordagens para o símbolo R$
 */

echo "🧪 TESTANDO DIFERENTES ABORDAGENS PARA R$\n\n";

// Teste 1: Símbolo direto
function formatMoney1($value) {
    return 'R$ ' . number_format((float)$value, 2, ',', '.');
}

// Teste 2: Com mb_convert_encoding
function formatMoney2($value) {
    $symbol = mb_convert_encoding('R$', 'UTF-8', 'auto');
    return $symbol . ' ' . number_format((float)$value, 2, ',', '.');
}

// Teste 3: Com chr() para construir o símbolo
function formatMoney3($value) {
    return 'R' . chr(36) . ' ' . number_format((float)$value, 2, ',', '.');
}

// Teste 4: Unicode direto
function formatMoney4($value) {
    return "\x52\x24 " . number_format((float)$value, 2, ',', '.');
}

// Teste 5: HTML entity decodificado
function formatMoney5($value) {
    return html_entity_decode('R&#36;', ENT_QUOTES, 'UTF-8') . ' ' . number_format((float)$value, 2, ',', '.');
}

$valor = 1234.56;

echo "Valor teste: $valor\n\n";
echo "Teste 1 (direto): " . formatMoney1($valor) . "\n";
echo "Teste 2 (mb_convert): " . formatMoney2($valor) . "\n";
echo "Teste 3 (chr): " . formatMoney3($valor) . "\n";
echo "Teste 4 (unicode): " . formatMoney4($valor) . "\n";
echo "Teste 5 (html_entity_decode): " . formatMoney5($valor) . "\n";

echo "\n🔍 INFORMAÇÕES DO SISTEMA:\n";
echo "Encoding interno: " . mb_internal_encoding() . "\n";
echo "Locale: " . setlocale(LC_ALL, 0) . "\n";

// Teste com diferentes locales
echo "\n🌍 TESTE COM LOCALE BRASILEIRO:\n";
if (setlocale(LC_MONETARY, 'pt_BR.UTF-8', 'pt_BR', 'portuguese')) {
    echo "Locale definido para português brasileiro\n";
    echo "money_format: " . money_format('%.2n', $valor) . "\n";
} else {
    echo "Locale brasileiro não disponível\n";
}

echo "\n💡 RECOMENDAÇÃO:\n";
echo "Use a função que exibir corretamente no seu teste!\n";
?>

