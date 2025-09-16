<?php
/**
 * Classe Database - Singleton para conexão com banco de dados
 * Sistema de Controle de Estoque - Pizzaria
 */

class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ]);
        } catch (PDOException $e) {
            throw new Exception("Erro na conexão com o banco de dados: " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    /**
     * Executa uma query e retorna os resultados
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            
            // Se é um SELECT, retorna os resultados
            if (stripos(trim($sql), 'SELECT') === 0) {
                return $stmt->fetchAll();
            }
            
            // Para INSERT, UPDATE, DELETE retorna true
            return true;
        } catch (PDOException $e) {
            debugLog("Erro na query: " . $e->getMessage(), ['sql' => $sql, 'params' => $params]);
            throw new Exception("Erro na execução da query: " . $e->getMessage());
        }
    }
    
    /**
     * Executa uma query e retorna apenas um resultado
     */
    public function fetchOne($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch();
        } catch (PDOException $e) {
            debugLog("Erro na query fetchOne: " . $e->getMessage(), ['sql' => $sql, 'params' => $params]);
            throw new Exception("Erro na execução da query: " . $e->getMessage());
        }
    }
    
    /**
     * Executa uma query e retorna todos os resultados
     */
    public function fetchAll($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            debugLog("Erro na query fetchAll: " . $e->getMessage(), ['sql' => $sql, 'params' => $params]);
            throw new Exception("Erro na execução da query: " . $e->getMessage());
        }
    }

    /**
     * Retorna o último ID inserido
     */
    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }
    
    /**
     * Inicia uma transação
     */
    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }
    
    /**
     * Confirma uma transação
     */
    public function commit() {
        return $this->connection->commit();
    }
    
    /**
     * Desfaz uma transação
     */
    public function rollback() {
        return $this->connection->rollback();
    }
    
    /**
     * Verifica se está em uma transação
     */
    public function inTransaction() {
        return $this->connection->inTransaction();
    }
    
    /**
     * Executa múltiplas queries em uma transação
     */
    public function transaction($callback) {
        try {
            $this->beginTransaction();
            $result = $callback($this);
            $this->commit();
            return $result;
        } catch (Exception $e) {
            if ($this->inTransaction()) {
                $this->rollback();
            }
            throw $e;
        }
    }
    
    // Previne clonagem
    private function __clone() {}
    
    // Previne deserialização
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}
?>

