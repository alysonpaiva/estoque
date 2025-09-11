<?php
/**
 * Configurações do Banco de Dados
 * Sistema de Controle de Estoque - Pizzaria
 */

// Configurações do banco de dados
define('DB_HOST', 'localhost');
define('DB_NAME', 'estoque_pizzaria');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Configurações do sistema
define('TIMEZONE', 'America/Sao_Paulo');
define('DATE_FORMAT', 'd/m/Y');
define('DATETIME_FORMAT', 'd/m/Y H:i:s');

// Configurações de estoque
define('ESTOQUE_MINIMO_ALERTA', 10); // Quantidade mínima para alerta
define('DECIMAL_PLACES', 2); // Casas decimais para valores monetários

// Configurar timezone
date_default_timezone_set(TIMEZONE);

// Configurações de erro (desabilitar em produção)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>

