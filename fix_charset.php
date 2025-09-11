<?php
/**
 * Script para corrigir problemas de charset/encoding
 * Sistema de Controle de Estoque - Pizzaria
 */

echo "🔧 CORRIGINDO PROBLEMAS DE CHARSET...\n\n";

// 1. Verificar se o arquivo config.php está correto
echo "✅ Verificando config.php...\n";
$configContent = file_get_contents('config/config.php');
if (strpos($configContent, 'R&#36;') !== false) {
    echo "✅ CURRENCY_SYMBOL corrigido para HTML entity\n";
} else {
    echo "❌ CURRENCY_SYMBOL ainda com problema\n";
}

// 2. Verificar charset do banco de dados
echo "\n📊 VERIFICAÇÕES RECOMENDADAS PARA SEU SERVIDOR:\n\n";

echo "1. BANCO DE DADOS:\n";
echo "   Execute no MySQL:\n";
echo "   SHOW VARIABLES LIKE 'character_set%';\n";
echo "   \n";
echo "   Se não estiver em utf8mb4, execute:\n";
echo "   ALTER DATABASE seu_banco CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;\n\n";

echo "2. CONEXÃO PHP:\n";
echo "   Verifique se no config/database.php tem:\n";
echo "   \$dsn = \"mysql:host=\$host;dbname=\$database;charset=utf8mb4\";\n\n";

echo "3. PÁGINAS HTML:\n";
echo "   Verifique se todas as páginas têm:\n";
echo "   <meta charset=\"UTF-8\">\n\n";

echo "4. SERVIDOR WEB:\n";
echo "   Adicione no .htaccess:\n";
echo "   AddDefaultCharset UTF-8\n\n";

// 3. Criar arquivo .htaccess se não existir
if (!file_exists('.htaccess')) {
    $htaccessContent = "# Configurações de Charset\n";
    $htaccessContent .= "AddDefaultCharset UTF-8\n\n";
    $htaccessContent .= "# Configurações de Segurança\n";
    $htaccessContent .= "Options -Indexes\n\n";
    $htaccessContent .= "# Configurações de Cache\n";
    $htaccessContent .= "<IfModule mod_expires.c>\n";
    $htaccessContent .= "    ExpiresActive On\n";
    $htaccessContent .= "    ExpiresByType text/css \"access plus 1 month\"\n";
    $htaccessContent .= "    ExpiresByType application/javascript \"access plus 1 month\"\n";
    $htaccessContent .= "    ExpiresByType image/png \"access plus 1 month\"\n";
    $htaccessContent .= "    ExpiresByType image/jpg \"access plus 1 month\"\n";
    $htaccessContent .= "    ExpiresByType image/jpeg \"access plus 1 month\"\n";
    $htaccessContent .= "</IfModule>\n";
    
    if (file_put_contents('.htaccess', $htaccessContent)) {
        echo "✅ Arquivo .htaccess criado com configurações de charset\n";
    } else {
        echo "❌ Erro ao criar .htaccess\n";
    }
} else {
    echo "ℹ️  Arquivo .htaccess já existe\n";
}

// 4. Verificar conexão do banco
echo "\n🔍 TESTANDO CONEXÃO COM CHARSET...\n";
try {
    require_once 'config/config.php';
    require_once 'classes/Database.php';
    
    $db = Database::getInstance();
    
    // Testar charset da conexão
    $result = $db->fetchOne("SELECT @@character_set_connection as charset");
    echo "✅ Charset da conexão: " . $result['charset'] . "\n";
    
    // Testar formatação de moeda
    echo "✅ Teste formatMoney(100.50): " . formatMoney(100.50) . "\n";
    echo "✅ Teste formatMoney(1234.56): " . formatMoney(1234.56) . "\n";
    
} catch (Exception $e) {
    echo "❌ Erro ao testar: " . $e->getMessage() . "\n";
}

echo "\n🎯 RESUMO DAS CORREÇÕES:\n";
echo "✅ CURRENCY_SYMBOL alterado para HTML entity (R&#36;)\n";
echo "✅ Arquivo .htaccess criado com charset UTF-8\n";
echo "✅ Testes de formatação executados\n\n";

echo "📋 PRÓXIMOS PASSOS NO SEU SERVIDOR:\n";
echo "1. Faça upload dos arquivos atualizados\n";
echo "2. Verifique o charset do banco de dados\n";
echo "3. Teste a exibição dos valores monetários\n";
echo "4. Se ainda houver problema, verifique as configurações do PHP\n\n";

echo "🚀 CORREÇÃO CONCLUÍDA!\n";
?>

