<?php
/**
 * Instalador Automático CORRIGIDO
 * Sistema de Controle de Estoque - Pizzaria
 */

// Verificar se já foi instalado
if (file_exists('config/installed.lock')) {
    die('Sistema já foi instalado. Para reinstalar, delete o arquivo config/installed.lock');
}

$erro = '';
$sucesso = '';
$step = isset($_GET['step']) ? (int) $_GET['step'] : 1;

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($step) {
        case 1:
            // Verificar requisitos
            $step = 2;
            break;
            
        case 2:
            // Configurar banco de dados
            $dbHost = $_POST['db_host'] ?? '';
            $dbName = $_POST['db_name'] ?? '';
            $dbUser = $_POST['db_user'] ?? '';
            $dbPass = $_POST['db_pass'] ?? '';
            $appName = $_POST['app_name'] ?? 'Sistema de Estoque';
            
            try {
                // Testar conexão
                $pdo = new PDO("mysql:host=$dbHost;charset=utf8mb4", $dbUser, $dbPass);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                // Criar banco se não existir
                $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                $pdo->exec("USE `$dbName`");
                
                // Executar SQL de criação das tabelas
                $sqlCommands = [
                    // Tabela produtos
                    "CREATE TABLE IF NOT EXISTS produtos (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        nome VARCHAR(100) NOT NULL,
                        unidade_medida VARCHAR(20) NOT NULL,
                        ativo BOOLEAN DEFAULT TRUE,
                        data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        INDEX idx_nome (nome),
                        INDEX idx_ativo (ativo)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
                    
                    // Tabela lotes
                    "CREATE TABLE IF NOT EXISTS lotes (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        produto_id INT NOT NULL,
                        preco_compra DECIMAL(10,2) NOT NULL,
                        quantidade_comprada DECIMAL(10,3) NOT NULL,
                        custo_por_unidade DECIMAL(10,4) NOT NULL,
                        data_compra TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        observacoes TEXT,
                        FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE,
                        INDEX idx_produto (produto_id),
                        INDEX idx_data (data_compra)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
                    
                    // Tabela producao
                    "CREATE TABLE IF NOT EXISTS producao (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        lote_id INT NOT NULL,
                        quantidade_produzida INT NOT NULL,
                        quantidade_materia_prima_usada DECIMAL(10,3) NOT NULL,
                        custo_total_producao DECIMAL(10,2) NOT NULL,
                        custo_por_porcao DECIMAL(10,4) NOT NULL,
                        data_producao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        observacoes TEXT,
                        FOREIGN KEY (lote_id) REFERENCES lotes(id) ON DELETE CASCADE,
                        INDEX idx_lote (lote_id),
                        INDEX idx_data (data_producao)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
                    
                    // Tabela retiradas
                    "CREATE TABLE IF NOT EXISTS retiradas (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        producao_id INT NOT NULL,
                        quantidade_retirada INT NOT NULL,
                        destino VARCHAR(100) NOT NULL,
                        responsavel VARCHAR(100),
                        data_retirada TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        observacoes TEXT,
                        FOREIGN KEY (producao_id) REFERENCES producao(id) ON DELETE CASCADE,
                        INDEX idx_producao (producao_id),
                        INDEX idx_destino (destino),
                        INDEX idx_data (data_retirada)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
                ];
                
                // Executar cada comando SQL
                foreach ($sqlCommands as $sql) {
                    $pdo->exec($sql);
                }
                
                // Criar arquivo de configuração
                $configContent = "<?php
/**
 * Configurações do Sistema
 * Gerado automaticamente pelo instalador
 */

// Configurações do Banco de Dados
define('DB_HOST', '$dbHost');
define('DB_NAME', '$dbName');
define('DB_USER', '$dbUser');
define('DB_PASS', '$dbPass');

// Configurações da Aplicação
define('APP_NAME', '$appName');
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

// Funções auxiliares
function sanitizeInput(\$input) {
    return htmlspecialchars(trim(\$input), ENT_QUOTES, 'UTF-8');
}

function formatDate(\$date, \$format = DATE_FORMAT) {
    if (\$date instanceof DateTime) {
        return \$date->format(\$format);
    }
    return date(\$format, strtotime(\$date));
}

function formatMoney(\$value) {
    return CURRENCY_SYMBOL . ' ' . number_format(\$value, 2, ',', '.');
}

function formatNumber(\$value, \$decimals = DECIMAL_PLACES) {
    return number_format(\$value, \$decimals, ',', '.');
}

function convertToDecimal(\$value) {
    return (float) str_replace(',', '.', str_replace('.', '', \$value));
}

function convertFromDecimal(\$value) {
    return number_format(\$value, DECIMAL_PLACES, ',', '');
}

// Autoload das classes
spl_autoload_register(function(\$className) {
    \$file = __DIR__ . '/classes/' . \$className . '.php';
    if (file_exists(\$file)) {
        require_once \$file;
    }
});
?>";
                
                file_put_contents('config/config.php', $configContent);
                
                // Criar arquivo de lock
                file_put_contents('config/installed.lock', date('Y-m-d H:i:s'));
                
                $sucesso = 'Sistema instalado com sucesso!';
                $step = 3;
                
            } catch (Exception $e) {
                $erro = 'Erro na instalação: ' . $e->getMessage();
            }
            break;
    }
}

function checkRequirement($name, $condition, $required = true) {
    $status = $condition ? 'OK' : 'ERRO';
    $class = $condition ? 'success' : ($required ? 'danger' : 'warning');
    $icon = $condition ? 'check' : 'times';
    
    echo "<tr class='table-$class'>";
    echo "<td>$name</td>";
    echo "<td><i class='fas fa-$icon'></i> $status</td>";
    echo "<td>" . ($required ? 'Obrigatório' : 'Recomendado') . "</td>";
    echo "</tr>";
    
    return $condition;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalação - Sistema de Controle de Estoque</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">
                            <i class="fas fa-cog"></i>
                            Instalação do Sistema de Controle de Estoque
                        </h4>
                    </div>
                    <div class="card-body">
                        <!-- Progress Bar -->
                        <div class="progress mb-4">
                            <div class="progress-bar" style="width: <?php echo ($step / 3) * 100; ?>%">
                                Passo <?php echo $step; ?> de 3
                            </div>
                        </div>

                        <?php if ($erro): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle"></i>
                                <?php echo $erro; ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($sucesso): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i>
                                <?php echo $sucesso; ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($step == 1): ?>
                            <!-- Passo 1: Verificação de Requisitos -->
                            <h5>Passo 1: Verificação de Requisitos</h5>
                            <p>Verificando se o servidor atende aos requisitos mínimos:</p>
                            
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Requisito</th>
                                        <th>Status</th>
                                        <th>Tipo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $allOk = true;
                                    $allOk &= checkRequirement('PHP 7.4+', version_compare(PHP_VERSION, '7.4.0', '>='));
                                    $allOk &= checkRequirement('Extensão PDO', extension_loaded('pdo'));
                                    $allOk &= checkRequirement('Extensão PDO MySQL', extension_loaded('pdo_mysql'));
                                    $allOk &= checkRequirement('Extensão JSON', extension_loaded('json'));
                                    $allOk &= checkRequirement('Extensão MBString', extension_loaded('mbstring'));
                                    checkRequirement('Extensão GD', extension_loaded('gd'), false);
                                    checkRequirement('Extensão cURL', extension_loaded('curl'), false);
                                    $allOk &= checkRequirement('Pasta config/ gravável', is_writable('config/'));
                                    ?>
                                </tbody>
                            </table>

                            <?php if ($allOk): ?>
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle"></i>
                                    Todos os requisitos obrigatórios foram atendidos!
                                </div>
                                <form method="POST">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-arrow-right"></i>
                                        Próximo Passo
                                    </button>
                                </form>
                            <?php else: ?>
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    Alguns requisitos obrigatórios não foram atendidos. 
                                    Corrija os problemas antes de continuar.
                                </div>
                            <?php endif; ?>

                        <?php elseif ($step == 2): ?>
                            <!-- Passo 2: Configuração do Banco de Dados -->
                            <h5>Passo 2: Configuração do Banco de Dados</h5>
                            <p>Configure a conexão com o banco de dados MySQL:</p>
                            
                            <form method="POST" class="needs-validation" novalidate>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="db_host" class="form-label">Servidor do Banco</label>
                                            <input type="text" class="form-control" id="db_host" name="db_host" 
                                                   value="localhost" required>
                                            <div class="invalid-feedback">
                                                Por favor, informe o servidor do banco.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="db_name" class="form-label">Nome do Banco</label>
                                            <input type="text" class="form-control" id="db_name" name="db_name" 
                                                   value="pizzaria_estoque" required>
                                            <div class="invalid-feedback">
                                                Por favor, informe o nome do banco.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="db_user" class="form-label">Usuário</label>
                                            <input type="text" class="form-control" id="db_user" name="db_user" 
                                                   value="root" required>
                                            <div class="invalid-feedback">
                                                Por favor, informe o usuário do banco.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="db_pass" class="form-label">Senha</label>
                                            <input type="password" class="form-control" id="db_pass" name="db_pass">
                                            <div class="form-text">
                                                Deixe em branco se não houver senha.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="app_name" class="form-label">Nome do Sistema</label>
                                    <input type="text" class="form-control" id="app_name" name="app_name" 
                                           value="Sistema de Controle de Estoque - Pizzaria" required>
                                    <div class="invalid-feedback">
                                        Por favor, informe o nome do sistema.
                                    </div>
                                </div>

                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <a href="?step=1" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left"></i>
                                        Voltar
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-database"></i>
                                        Instalar Sistema
                                    </button>
                                </div>
                            </form>

                        <?php elseif ($step == 3): ?>
                            <!-- Passo 3: Instalação Concluída -->
                            <div class="text-center">
                                <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                                <h3 class="mt-3">Instalação Concluída!</h3>
                                <p class="lead">O sistema foi instalado com sucesso e está pronto para uso.</p>
                                
                                <div class="alert alert-info">
                                    <h6><i class="fas fa-info-circle"></i> Próximos Passos:</h6>
                                    <ol class="text-start">
                                        <li>Delete o arquivo <code>install.php</code> por segurança</li>
                                        <li>Acesse o sistema através do arquivo <code>index.php</code></li>
                                        <li>Comece cadastrando seus produtos</li>
                                        <li>Registre os lotes de matéria-prima</li>
                                        <li>Faça suas primeiras produções</li>
                                    </ol>
                                </div>
                                
                                <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                                    <a href="index.php" class="btn btn-success btn-lg">
                                        <i class="fas fa-home"></i>
                                        Acessar Sistema
                                    </a>
                                    <a href="teste.php" class="btn btn-outline-primary">
                                        <i class="fas fa-vial"></i>
                                        Executar Testes
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Validação do formulário
        (function() {
            'use strict';
            window.addEventListener('load', function() {
                var forms = document.getElementsByClassName('needs-validation');
                var validation = Array.prototype.filter.call(forms, function(form) {
                    form.addEventListener('submit', function(event) {
                        if (form.checkValidity() === false) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                        form.classList.add('was-validated');
                    }, false);
                });
            }, false);
        })();
    </script>
</body>
</html>

