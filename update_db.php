<?php
require_once 'config/config.php';

try {
    $db = Database::getInstance();
    $sql = file_get_contents('sql/update_database.sql');
    $statements = explode(';', $sql);
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement)) {
            $db->query($statement);
            echo 'Executado: ' . substr($statement, 0, 50) . '...' . PHP_EOL;
        }
    }
    
    echo 'Banco de dados atualizado com sucesso!' . PHP_EOL;
} catch (Exception $e) {
    echo 'Erro: ' . $e->getMessage() . PHP_EOL;
}
?>

