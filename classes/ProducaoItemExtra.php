<?php
/**
 * Classe ProducaoItemExtra
 * Para itens extras utilizados na produção (temperos, gás, etc.)
 * Sistema de Controle de Estoque - Pizzaria
 */

class ProducaoItemExtra {
    private $id;
    private $producaoId;
    private $descricao;
    private $quantidade;
    private $unidade;
    private $valorUnitario;
    private $valorTotal;
    private $db;
    
    /**
     * Construtor
     */
    public function __construct($producaoId = null, $descricao = null, $quantidade = null, $valorUnitario = null) {
        $this->db = Database::getInstance();
        
        if ($producaoId !== null && $descricao !== null && $quantidade !== null && $valorUnitario !== null) {
            $this->producaoId = $producaoId;
            $this->descricao = $descricao;
            $this->quantidade = $quantidade;
            $this->valorUnitario = $valorUnitario;
            $this->valorTotal = $quantidade * $valorUnitario;
        }
    }
    
    // Getters
    public function getId() { return $this->id; }
    public function getProducaoId() { return $this->producaoId; }
    public function getDescricao() { return $this->descricao; }
    public function getQuantidade() { return $this->quantidade; }
    public function getUnidade() { return $this->unidade; }
    public function getValorUnitario() { return $this->valorUnitario; }
    public function getValorTotal() { return $this->valorTotal; }
    
    // Setters
    public function setId($id) { $this->id = $id; }
    public function setProducaoId($producaoId) { $this->producaoId = $producaoId; }
    public function setDescricao($descricao) { $this->descricao = $descricao; }
    public function setQuantidade($quantidade) { 
        $this->quantidade = $quantidade;
        $this->calcularValorTotal();
    }
    public function setUnidade($unidade) { $this->unidade = $unidade; }
    public function setValorUnitario($valorUnitario) { 
        $this->valorUnitario = $valorUnitario;
        $this->calcularValorTotal();
    }
    
    /**
     * Calcula o valor total
     */
    private function calcularValorTotal() {
        if ($this->quantidade !== null && $this->valorUnitario !== null) {
            $this->valorTotal = $this->quantidade * $this->valorUnitario;
        }
    }
    
    /**
     * Valida os dados do item extra
     */
    private function validar() {
        $erros = [];
        
        if (empty($this->producaoId)) {
            $erros[] = 'ID da produção é obrigatório';
        }
        
        if (empty($this->descricao)) {
            $erros[] = 'Descrição é obrigatória';
        }
        
        if (empty($this->quantidade) || $this->quantidade <= 0) {
            $erros[] = 'Quantidade deve ser maior que zero';
        }
        
        if (empty($this->valorUnitario) || $this->valorUnitario <= 0) {
            $erros[] = 'Valor unitário deve ser maior que zero';
        }
        
        if (empty($this->unidade)) {
            $erros[] = 'Unidade é obrigatória';
        }
        
        return $erros;
    }
    
    /**
     * Salva o item extra no banco de dados
     */
    public function salvar() {
        try {
            $erros = $this->validar();
            if (!empty($erros)) {
                throw new Exception('Dados inválidos: ' . implode(', ', $erros));
            }
            
            if ($this->id) {
                // Atualizar item existente
                $sql = "UPDATE producao_itens_extras SET 
                        producao_id = :producao_id,
                        descricao = :descricao,
                        quantidade = :quantidade,
                        unidade = :unidade,
                        valor_unitario = :valor_unitario,
                        valor_total = :valor_total
                        WHERE id = :id";
                
                $params = [
                    ':id' => $this->id,
                    ':producao_id' => $this->producaoId,
                    ':descricao' => $this->descricao,
                    ':quantidade' => $this->quantidade,
                    ':unidade' => $this->unidade,
                    ':valor_unitario' => $this->valorUnitario,
                    ':valor_total' => $this->valorTotal
                ];
            } else {
                // Inserir novo item
                $sql = "INSERT INTO producao_itens_extras (producao_id, descricao, quantidade, unidade, valor_unitario, valor_total)
                        VALUES (:producao_id, :descricao, :quantidade, :unidade, :valor_unitario, :valor_total)";
                
                $params = [
                    ':producao_id' => $this->producaoId,
                    ':descricao' => $this->descricao,
                    ':quantidade' => $this->quantidade,
                    ':unidade' => $this->unidade,
                    ':valor_unitario' => $this->valorUnitario,
                    ':valor_total' => $this->valorTotal
                ];
            }
            
            $this->db->query($sql, $params);
            
            if (!$this->id) {
                $this->id = $this->db->getLastInsertId();
            }
            
            return true;
        } catch (Exception $e) {
            debugLog("Erro ao salvar item extra: " . $e->getMessage(), [
                'producao_id' => $this->producaoId,
                'descricao' => $this->descricao
            ]);
            return false;
        }
    }
    
    /**
     * Busca item por ID
     */
    public static function buscarPorId($id) {
        try {
            $db = Database::getInstance();
            $sql = "SELECT * FROM producao_itens_extras WHERE id = :id";
            $result = $db->fetchOne($sql, [':id' => $id]);
            
            if ($result) {
                $item = new self();
                $item->id = $result['id'];
                $item->producaoId = $result['producao_id'];
                $item->descricao = $result['descricao'];
                $item->quantidade = $result['quantidade'];
                $item->unidade = $result['unidade'];
                $item->valorUnitario = $result['valor_unitario'];
                $item->valorTotal = $result['valor_total'];
                
                return $item;
            }
            
            return null;
        } catch (Exception $e) {
            debugLog("Erro ao buscar item extra: " . $e->getMessage(), ['id' => $id]);
            return null;
        }
    }
    
    /**
     * Lista itens por produção
     */
    public static function listarPorProducao($producaoId) {
        try {
            $db = Database::getInstance();
            $sql = "SELECT * FROM producao_itens_extras WHERE producao_id = :producao_id ORDER BY descricao";
            $results = $db->fetchAll($sql, [':producao_id' => $producaoId]);
            
            $itens = [];
            foreach ($results as $result) {
                $item = new self();
                $item->id = $result['id'];
                $item->producaoId = $result['producao_id'];
                $item->descricao = $result['descricao'];
                $item->quantidade = $result['quantidade'];
                $item->unidade = $result['unidade'];
                $item->valorUnitario = $result['valor_unitario'];
                $item->valorTotal = $result['valor_total'];
                
                $itens[] = $item;
            }
            
            return $itens;
        } catch (Exception $e) {
            debugLog("Erro ao listar itens extras: " . $e->getMessage(), ['producao_id' => $producaoId]);
            return [];
        }
    }
    
    /**
     * Calcula total de itens extras por produção
     */
    public static function calcularTotalPorProducao($producaoId) {
        try {
            $db = Database::getInstance();
            $sql = "SELECT SUM(valor_total) as total FROM producao_itens_extras WHERE producao_id = :producao_id";
            $result = $db->fetchOne($sql, [':producao_id' => $producaoId]);
            
            return $result ? (float)$result['total'] : 0;
        } catch (Exception $e) {
            debugLog("Erro ao calcular total de itens extras: " . $e->getMessage(), ['producao_id' => $producaoId]);
            return 0;
        }
    }
    
    /**
     * Exclui o item extra
     */
    public function excluir() {
        try {
            if (!$this->id) {
                throw new Exception('ID do item não definido');
            }
            
            $sql = "DELETE FROM producao_itens_extras WHERE id = :id";
            $this->db->query($sql, [':id' => $this->id]);
            
            return true;
        } catch (Exception $e) {
            debugLog("Erro ao excluir item extra: " . $e->getMessage(), ['id' => $this->id]);
            return false;
        }
    }
    
    /**
     * Exclui todos os itens de uma produção
     */
    public static function excluirPorProducao($producaoId) {
        try {
            $db = Database::getInstance();
            $sql = "DELETE FROM producao_itens_extras WHERE producao_id = :producao_id";
            $db->query($sql, [':producao_id' => $producaoId]);
            
            return true;
        } catch (Exception $e) {
            debugLog("Erro ao excluir itens extras da produção: " . $e->getMessage(), ['producao_id' => $producaoId]);
            return false;
        }
    }
    
    /**
     * Lista tipos de itens mais utilizados (para sugestões)
     */
    public static function listarTiposComuns($limite = 10) {
        try {
            $db = Database::getInstance();
            $sql = "SELECT descricao, unidade, AVG(valor_unitario) as valor_medio, COUNT(*) as frequencia
                    FROM producao_itens_extras 
                    GROUP BY descricao, unidade 
                    ORDER BY frequencia DESC, descricao 
                    LIMIT :limite";
            
            return $db->fetchAll($sql, [':limite' => $limite]);
        } catch (Exception $e) {
            debugLog("Erro ao listar tipos comuns: " . $e->getMessage());
            return [];
        }
    }
}
?>

