<?php
/**
 * Classe Produto
 * Sistema de Controle de Estoque - Pizzaria
 */

class Produto {
    private $id;
    private $nome;
    private $unidadeMedida;
    private $ativo;
    private $dataCadastro;
    private $db;
    
    /**
     * Construtor
     */
    public function __construct($nome = null, $unidadeMedida = null) {
        $this->db = Database::getInstance();
        $this->ativo = true;
        $this->dataCadastro = new DateTime();
        
        if ($nome !== null && $unidadeMedida !== null) {
            $this->nome = $nome;
            $this->unidadeMedida = $unidadeMedida;
        }
    }
    
    // Getters
    public function getId() { return $this->id; }
    public function getNome() { return $this->nome; }
    public function getUnidadeMedida() { return $this->unidadeMedida; }
    public function getAtivo() { return $this->ativo; }
    public function getDataCadastro() { return $this->dataCadastro; }
    
    // Setters
    public function setId($id) { $this->id = $id; }
    public function setNome($nome) { $this->nome = $nome; }
    public function setUnidadeMedida($unidadeMedida) { $this->unidadeMedida = $unidadeMedida; }
    public function setAtivo($ativo) { $this->ativo = $ativo; }
    public function setDataCadastro($dataCadastro) {
        if ($dataCadastro instanceof DateTime) {
            $this->dataCadastro = $dataCadastro;
        } else {
            $this->dataCadastro = new DateTime($dataCadastro);
        }
    }
    
    /**
     * Valida os dados do produto
     */
    public function validar() {
        $erros = [];
        
        if (empty($this->nome)) {
            $erros[] = 'Nome é obrigatório';
        } elseif (strlen($this->nome) < 2) {
            $erros[] = 'Nome deve ter pelo menos 2 caracteres';
        } elseif (strlen($this->nome) > 100) {
            $erros[] = 'Nome deve ter no máximo 100 caracteres';
        }
        
        if (empty($this->unidadeMedida)) {
            $erros[] = 'Unidade de medida é obrigatória';
        } elseif (strlen($this->unidadeMedida) > 20) {
            $erros[] = 'Unidade de medida deve ter no máximo 20 caracteres';
        }
        
        return $erros;
    }
    
    /**
     * Salva o produto no banco de dados
     */
    public function salvar() {
        try {
            $erros = $this->validar();
            if (!empty($erros)) {
                throw new Exception('Dados inválidos: ' . implode(', ', $erros));
            }
            
            if ($this->id) {
                // Atualizar produto existente
                $sql = "UPDATE produtos SET 
                        nome = :nome,
                        unidade_medida = :unidade_medida,
                        ativo = :ativo
                        WHERE id = :id";
                
                $params = [
                    ':id' => $this->id,
                    ':nome' => $this->nome,
                    ':unidade_medida' => $this->unidadeMedida,
                    ':ativo' => $this->ativo ? 1 : 0
                ];
            } else {
                // Inserir novo produto
                $sql = "INSERT INTO produtos (nome, unidade_medida, ativo, data_cadastro)
                        VALUES (:nome, :unidade_medida, :ativo, :data_cadastro)";
                
                $params = [
                    ':nome' => $this->nome,
                    ':unidade_medida' => $this->unidadeMedida,
                    ':ativo' => $this->ativo ? 1 : 0,
                    ':data_cadastro' => $this->dataCadastro->format('Y-m-d H:i:s')
                ];
            }
            
            $this->db->query($sql, $params);
            
            if (!$this->id) {
                $this->id = $this->db->lastInsertId();
            }
            
            return true;
        } catch (Exception $e) {
            debugLog("Erro ao salvar produto: " . $e->getMessage(), $this);
            return false;
        }
    }
    
    /**
     * Busca produto por ID
     */
    public static function buscarPorId($id) {
        try {
            $db = Database::getInstance();
            $sql = "SELECT * FROM produtos WHERE id = :id";
            $result = $db->fetchOne($sql, [':id' => $id]);
            
            if ($result) {
                $produto = new self();
                $produto->id = $result['id'];
                $produto->nome = $result['nome'];
                $produto->unidadeMedida = $result['unidade_medida'];
                $produto->ativo = (bool)$result['ativo'];
                $produto->dataCadastro = new DateTime($result['data_cadastro']);
                
                return $produto;
            }
            
            return null;
        } catch (Exception $e) {
            debugLog("Erro ao buscar produto: " . $e->getMessage(), ['id' => $id]);
            return null;
        }
    }
    
    /**
     * Lista todos os produtos
     */
    public static function listarTodos($apenasAtivos = false) {
        try {
            $db = Database::getInstance();
            $sql = "SELECT * FROM produtos";
            
            if ($apenasAtivos) {
                $sql .= " WHERE ativo = 1";
            }
            
            $sql .= " ORDER BY nome";
            
            $results = $db->query($sql);
            $produtos = [];
            
            foreach ($results as $row) {
                $produto = new self();
                $produto->id = $row['id'];
                $produto->nome = $row['nome'];
                $produto->unidadeMedida = $row['unidade_medida'];
                $produto->ativo = (bool)$row['ativo'];
                $produto->dataCadastro = new DateTime($row['data_cadastro']);
                
                $produtos[] = $produto;
            }
            
            return $produtos;
        } catch (Exception $e) {
            debugLog("Erro ao listar produtos: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Lista apenas produtos ativos
     */
    public static function listarAtivos() {
        return self::listarTodos(true);
    }
    
    /**
     * Busca produtos por nome
     */
    public static function buscarPorNome($nome) {
        try {
            $db = Database::getInstance();
            $sql = "SELECT * FROM produtos WHERE nome LIKE :nome AND ativo = 1 ORDER BY nome";
            $results = $db->query($sql, [':nome' => '%' . $nome . '%']);
            
            $produtos = [];
            foreach ($results as $row) {
                $produto = new self();
                $produto->id = $row['id'];
                $produto->nome = $row['nome'];
                $produto->unidadeMedida = $row['unidade_medida'];
                $produto->ativo = (bool)$row['ativo'];
                $produto->dataCadastro = new DateTime($row['data_cadastro']);
                
                $produtos[] = $produto;
            }
            
            return $produtos;
        } catch (Exception $e) {
            debugLog("Erro ao buscar produtos por nome: " . $e->getMessage(), ['nome' => $nome]);
            return [];
        }
    }
    
    /**
     * Desativa o produto (soft delete)
     */
    public function desativar() {
        try {
            if (!$this->id) {
                throw new Exception('Produto não foi salvo ainda');
            }
            
            $sql = "UPDATE produtos SET ativo = 0 WHERE id = :id";
            $this->db->query($sql, [':id' => $this->id]);
            $this->ativo = false;
            
            return true;
        } catch (Exception $e) {
            debugLog("Erro ao desativar produto: " . $e->getMessage(), $this);
            return false;
        }
    }
    
    /**
     * Ativa o produto
     */
    public function ativar() {
        try {
            if (!$this->id) {
                throw new Exception('Produto não foi salvo ainda');
            }
            
            $sql = "UPDATE produtos SET ativo = 1 WHERE id = :id";
            $this->db->query($sql, [':id' => $this->id]);
            $this->ativo = true;
            
            return true;
        } catch (Exception $e) {
            debugLog("Erro ao ativar produto: " . $e->getMessage(), $this);
            return false;
        }
    }
    
    /**
     * Verifica se o produto pode ser excluído
     */
    public function podeExcluir() {
        try {
            if (!$this->id) {
                return false;
            }
            
            // Verifica se há lotes vinculados
            $sql = "SELECT COUNT(*) as total FROM lotes WHERE produto_id = :produto_id";
            $result = $this->db->fetchOne($sql, [':produto_id' => $this->id]);
            
            return $result['total'] == 0;
        } catch (Exception $e) {
            debugLog("Erro ao verificar se produto pode ser excluído: " . $e->getMessage(), $this);
            return false;
        }
    }
    
    /**
     * Exclui o produto permanentemente
     */
    public function excluir() {
        try {
            if (!$this->id) {
                throw new Exception('Produto não foi salvo ainda');
            }
            
            if (!$this->podeExcluir()) {
                throw new Exception('Produto não pode ser excluído pois possui lotes vinculados');
            }
            
            $sql = "DELETE FROM produtos WHERE id = :id";
            $this->db->query($sql, [':id' => $this->id]);
            
            return true;
        } catch (Exception $e) {
            debugLog("Erro ao excluir produto: " . $e->getMessage(), $this);
            return false;
        }
    }
}
?>

