<?php
/**
 * Configurações do Sistema
 * Sistema de Controle de Estoque - Pizzaria
 */

// Configurações do Banco de Dados
define('DB_HOST', 'localhost');
define('DB_NAME', 'pizzaria_estoque');
define('DB_USER', 'root');
define('DB_PASS', '');

// Configurações da Aplicação
define('APP_NAME', 'Sistema de Controle de Estoque - Pizzaria');
define('APP_VERSION', '1.0.0');
define('TIMEZONE', 'America/Sao_Paulo');

// Configurações de Formato
define('DATE_FORMAT', 'd/m/Y');
define('DATETIME_FORMAT', 'd/m/Y H:i:s');
define('DECIMAL_PLACES', 3);
define('CURRENCY_SYMBOL', 'R$');

// Configurações de Estoque
define('ESTOQUE_MINIMO_ALERTA', 10);

// Configurar timezone
date_default_timezone_set(TIMEZONE);

// Caminhos
define('ROOT_PATH', dirname(__DIR__) . '/');
define('CLASSES_PATH', ROOT_PATH . 'classes/');
define('PAGES_PATH', ROOT_PATH . 'pages/');
define('ASSETS_PATH', ROOT_PATH . 'assets/');

// Função para autoload das classes
spl_autoload_register(function ($class_name) {
    $file = CLASSES_PATH . $class_name . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Função para formatar valores monetários
function formatMoney($value) {
    if ($value === null || $value === '' || !is_numeric($value)) {
        $value = 0;
    }
    return CURRENCY_SYMBOL . ' ' . number_format((float)$value, 2, ',', '.');
}

// Função para formatar datas
function formatDate($date, $format = DATE_FORMAT) {
    if (empty($date)) {
        return '-';
    }
    if ($date instanceof DateTime) {
        return $date->format($format);
    }
    try {
        return date($format, strtotime($date));
    } catch (Exception $e) {
        return '-';
    }
}

// Função para formatar números
function formatNumber($number, $decimals = DECIMAL_PLACES) {
    if ($number === null || $number === '' || !is_numeric($number)) {
        $number = 0;
    }
    return number_format((float)$number, $decimals, ',', '.');
}

// Função para sanitizar entrada
function sanitizeInput($input) {
    if ($input === null) {
        return '';
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Função para validar números decimais
function validateDecimal($value, $min = 0) {
    if (empty($value)) {
        return false;
    }
    $value = str_replace(',', '.', $value);
    return is_numeric($value) && (float)$value >= $min;
}

// Função para converter vírgula em ponto (formato brasileiro para banco)
function convertToDecimal($value) {
    if (empty($value)) {
        return 0;
    }
    return (float)str_replace(',', '.', str_replace('.', '', $value));
}

// Função para converter ponto em vírgula (banco para formato brasileiro)
function convertFromDecimal($value) {
    if (empty($value)) {
        return '0,00';
    }
    return number_format((float)$value, 2, ',', '.');
}

// Função para debug seguro
function debugLog($message, $data = null) {
    if (defined('DEBUG') && DEBUG) {
        error_log("DEBUG: $message" . ($data ? ' - ' . print_r($data, true) : ''));
    }
}

// Iniciar sessão se não estiver iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Configurar exibição de erros para desenvolvimento
if (defined('DEBUG') && DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ERROR | E_WARNING | E_PARSE);
    ini_set('display_errors', 0);
}
?>

