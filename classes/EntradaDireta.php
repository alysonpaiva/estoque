<?php
/**
 * Classe EntradaDireta
 * Para produtos prontos que não necessitam de produção
 * Sistema de Controle de Estoque - Pizzaria
 */

class EntradaDireta {
    private $id;
    private $produtoId;
    private $quantidadeEntrada;
    private $precoUnitario;
    private $valorTotal;
    private $fornecedor;
    private $notaFiscal;
    private $dataEntrada;
    private $observacoes;
    private $db;
    
    /**
     * Construtor
     */
    public function __construct($produtoId = null, $quantidadeEntrada = null, $precoUnitario = null) {
        $this->db = Database::getInstance();
        $this->dataEntrada = new DateTime();
        
        if ($produtoId !== null && $quantidadeEntrada !== null && $precoUnitario !== null) {
            $this->produtoId = $produtoId;
            $this->quantidadeEntrada = $quantidadeEntrada;
            $this->precoUnitario = $precoUnitario;
            $this->valorTotal = $quantidadeEntrada * $precoUnitario;
        }
    }
    
    // Getters
    public function getId() { return $this->id; }
    public function getProdutoId() { return $this->produtoId; }
    public function getQuantidadeEntrada() { return $this->quantidadeEntrada; }
    public function getPrecoUnitario() { return $this->precoUnitario; }
    public function getValorTotal() { return $this->valorTotal; }
    public function getFornecedor() { return $this->fornecedor; }
    public function getNotaFiscal() { return $this->notaFiscal; }
    public function getDataEntrada() { return $this->dataEntrada; }
    public function getObservacoes() { return $this->observacoes; }
    
    // Setters
    public function setId($id) { $this->id = $id; }
    public function setProdutoId($produtoId) { $this->produtoId = $produtoId; }
    public function setQuantidadeEntrada($quantidadeEntrada) { 
        $this->quantidadeEntrada = $quantidadeEntrada;
        $this->calcularValorTotal();
    }
    public function setPrecoUnitario($precoUnitario) { 
        $this->precoUnitario = $precoUnitario;
        $this->calcularValorTotal();
    }
    public function setFornecedor($fornecedor) { $this->fornecedor = $fornecedor; }
    public function setNotaFiscal($notaFiscal) { $this->notaFiscal = $notaFiscal; }
    public function setDataEntrada($dataEntrada) {
        if ($dataEntrada instanceof DateTime) {
            $this->dataEntrada = $dataEntrada;
        } else {
            $this->dataEntrada = new DateTime($dataEntrada);
        }
    }
    public function setObservacoes($observacoes) { $this->observacoes = $observacoes; }
    
    /**
     * Calcula o valor total
     */
    private function calcularValorTotal() {
        if ($this->quantidadeEntrada !== null && $this->precoUnitario !== null) {
            $this->valorTotal = $this->quantidadeEntrada * $this->precoUnitario;
        }
    }
    
    /**
     * Valida os dados da entrada
     */
    private function validar() {
        $erros = [];
        
        if (empty($this->produtoId)) {
            $erros[] = 'Produto é obrigatório';
        }
        
        if (empty($this->quantidadeEntrada) || $this->quantidadeEntrada <= 0) {
            $erros[] = 'Quantidade deve ser maior que zero';
        }
        
        if (empty($this->precoUnitario) || $this->precoUnitario <= 0) {
            $erros[] = 'Preço unitário deve ser maior que zero';
        }
        
        return $erros;
    }
    
    /**
     * Salva a entrada no banco de dados
     */
    public function salvar() {
        try {
            $erros = $this->validar();
            if (!empty($erros)) {
                throw new Exception('Dados inválidos: ' . implode(', ', $erros));
            }
            
            if ($this->id) {
                // Atualizar entrada existente
                $sql = "UPDATE entradas_diretas SET 
                        produto_id = :produto_id,
                        quantidade_entrada = :quantidade_entrada,
                        preco_unitario = :preco_unitario,
                        valor_total = :valor_total,
                        fornecedor = :fornecedor,
                        nota_fiscal = :nota_fiscal,
                        observacoes = :observacoes
                        WHERE id = :id";
                
                $params = [
                    ':id' => $this->id,
                    ':produto_id' => $this->produtoId,
                    ':quantidade_entrada' => $this->quantidadeEntrada,
                    ':preco_unitario' => $this->precoUnitario,
                    ':valor_total' => $this->valorTotal,
                    ':fornecedor' => $this->fornecedor,
                    ':nota_fiscal' => $this->notaFiscal,
                    ':observacoes' => $this->observacoes
                ];
            } else {
                // Inserir nova entrada
                $sql = "INSERT INTO entradas_diretas (produto_id, quantidade_entrada, preco_unitario, valor_total, fornecedor, nota_fiscal, data_entrada, observacoes)
                        VALUES (:produto_id, :quantidade_entrada, :preco_unitario, :valor_total, :fornecedor, :nota_fiscal, :data_entrada, :observacoes)";
                
                $params = [
                    ':produto_id' => $this->produtoId,
                    ':quantidade_entrada' => $this->quantidadeEntrada,
                    ':preco_unitario' => $this->precoUnitario,
                    ':valor_total' => $this->valorTotal,
                    ':fornecedor' => $this->fornecedor,
                    ':nota_fiscal' => $this->notaFiscal,
                    ':data_entrada' => $this->dataEntrada->format('Y-m-d H:i:s'),
                    ':observacoes' => $this->observacoes
                ];
            }
            
            $this->db->query($sql, $params);
            
            if (!$this->id) {
                $this->id = $this->db->getLastInsertId();
            }
            
            return true;
        } catch (Exception $e) {
            debugLog("Erro ao salvar entrada direta: " . $e->getMessage(), [
                'produto_id' => $this->produtoId,
                'quantidade' => $this->quantidadeEntrada
            ]);
            return false;
        }
    }
    
    /**
     * Busca entrada por ID
     */
    public static function buscarPorId($id) {
        try {
            $db = Database::getInstance();
            $sql = "SELECT * FROM entradas_diretas WHERE id = :id";
            $result = $db->fetchOne($sql, [':id' => $id]);
            
            if ($result) {
                $entrada = new self();
                $entrada->id = $result['id'];
                $entrada->produtoId = $result['produto_id'];
                $entrada->quantidadeEntrada = $result['quantidade_entrada'];
                $entrada->precoUnitario = $result['preco_unitario'];
                $entrada->valorTotal = $result['valor_total'];
                $entrada->fornecedor = $result['fornecedor'];
                $entrada->notaFiscal = $result['nota_fiscal'];
                $entrada->dataEntrada = new DateTime($result['data_entrada']);
                $entrada->observacoes = $result['observacoes'];
                
                return $entrada;
            }
            
            return null;
        } catch (Exception $e) {
            debugLog("Erro ao buscar entrada direta: " . $e->getMessage(), ['id' => $id]);
            return null;
        }
    }
    
    /**
     * Lista todas as entradas
     */
    public static function listar($produtoId = null, $limite = null) {
        try {
            $db = Database::getInstance();
            
            $sql = "SELECT ed.*, p.nome as produto_nome, p.unidade_medida 
                    FROM entradas_diretas ed
                    LEFT JOIN produtos p ON ed.produto_id = p.id";
            
            $params = [];
            
            if ($produtoId) {
                $sql .= " WHERE ed.produto_id = :produto_id";
                $params[':produto_id'] = $produtoId;
            }
            
            $sql .= " ORDER BY ed.data_entrada DESC";
            
            if ($limite) {
                $sql .= " LIMIT :limite";
                $params[':limite'] = $limite;
            }
            
            $results = $db->fetchAll($sql, $params);
            $entradas = [];
            
            foreach ($results as $result) {
                $entrada = new self();
                $entrada->id = $result['id'];
                $entrada->produtoId = $result['produto_id'];
                $entrada->quantidadeEntrada = $result['quantidade_entrada'];
                $entrada->precoUnitario = $result['preco_unitario'];
                $entrada->valorTotal = $result['valor_total'];
                $entrada->fornecedor = $result['fornecedor'];
                $entrada->notaFiscal = $result['nota_fiscal'];
                $entrada->dataEntrada = new DateTime($result['data_entrada']);
                $entrada->observacoes = $result['observacoes'];
                
                // Adicionar informações do produto
                $entrada->produtoNome = $result['produto_nome'];
                $entrada->unidadeMedida = $result['unidade_medida'];
                
                $entradas[] = $entrada;
            }
            
            return $entradas;
        } catch (Exception $e) {
            debugLog("Erro ao listar entradas diretas: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Exclui a entrada
     */
    public function excluir() {
        try {
            if (!$this->id) {
                throw new Exception('ID da entrada não definido');
            }
            
            $sql = "DELETE FROM entradas_diretas WHERE id = :id";
            $this->db->query($sql, [':id' => $this->id]);
            
            return true;
        } catch (Exception $e) {
            debugLog("Erro ao excluir entrada direta: " . $e->getMessage(), ['id' => $this->id]);
            return false;
        }
    }
    
    /**
     * Obtém o produto associado
     */
    public function getProduto() {
        if ($this->produtoId) {
            return Produto::buscarPorId($this->produtoId);
        }
        return null;
    }
}
?>

