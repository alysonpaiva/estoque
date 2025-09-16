<?php
/**
 * Classe RetiradaDireta
 * Para retiradas de produtos prontos
 * Sistema de Controle de Estoque - Pizzaria
 */

class RetiradaDireta {
    private $id;
    private $produtoId;
    private $quantidadeRetirada;
    private $destino;
    private $responsavel;
    private $dataRetirada;
    private $observacoes;
    private $db;
    
    /**
     * Construtor
     */
    public function __construct($produtoId = null, $quantidadeRetirada = null, $destino = null) {
        $this->db = Database::getInstance();
        $this->dataRetirada = new DateTime();
        
        if ($produtoId !== null && $quantidadeRetirada !== null && $destino !== null) {
            $this->produtoId = $produtoId;
            $this->quantidadeRetirada = $quantidadeRetirada;
            $this->destino = $destino;
        }
    }
    
    // Getters
    public function getId() { return $this->id; }
    public function getProdutoId() { return $this->produtoId; }
    public function getQuantidadeRetirada() { return $this->quantidadeRetirada; }
    public function getDestino() { return $this->destino; }
    public function getResponsavel() { return $this->responsavel; }
    public function getDataRetirada() { return $this->dataRetirada; }
    public function getObservacoes() { return $this->observacoes; }
    
    // Setters
    public function setId($id) { $this->id = $id; }
    public function setProdutoId($produtoId) { $this->produtoId = $produtoId; }
    public function setQuantidadeRetirada($quantidadeRetirada) { $this->quantidadeRetirada = $quantidadeRetirada; }
    public function setDestino($destino) { $this->destino = $destino; }
    public function setResponsavel($responsavel) { $this->responsavel = $responsavel; }
    public function setDataRetirada($dataRetirada) {
        if ($dataRetirada instanceof DateTime) {
            $this->dataRetirada = $dataRetirada;
        } else {
            $this->dataRetirada = new DateTime($dataRetirada);
        }
    }
    public function setObservacoes($observacoes) { $this->observacoes = $observacoes; }
    
    /**
     * Valida os dados da retirada
     */
    private function validar() {
        $erros = [];
        
        if (empty($this->produtoId)) {
            $erros[] = 'Produto é obrigatório';
        }
        
        if (empty($this->quantidadeRetirada) || $this->quantidadeRetirada <= 0) {
            $erros[] = 'Quantidade deve ser maior que zero';
        }
        
        if (empty($this->destino)) {
            $erros[] = 'Destino é obrigatório';
        }
        
        // Verificar se há estoque suficiente
        $estoqueDisponivel = $this->getEstoqueDisponivel();
        if ($this->quantidadeRetirada > $estoqueDisponivel) {
            $erros[] = "Estoque insuficiente. Disponível: {$estoqueDisponivel}";
        }
        
        return $erros;
    }
    
    /**
     * Obtém o estoque disponível do produto
     */
    private function getEstoqueDisponivel() {
        try {
            $sql = "SELECT 
                        COALESCE(SUM(ed.quantidade_entrada), 0) - COALESCE(SUM(rd.quantidade_retirada), 0) as estoque
                    FROM produtos p
                    LEFT JOIN entradas_diretas ed ON p.id = ed.produto_id
                    LEFT JOIN retiradas_diretas rd ON p.id = rd.produto_id
                    WHERE p.id = :produto_id AND p.tipo_produto = 'produto_pronto'";
            
            $result = $this->db->fetchOne($sql, [':produto_id' => $this->produtoId]);
            return $result ? (float)$result['estoque'] : 0;
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * Salva a retirada no banco de dados
     */
    public function salvar() {
        try {
            $erros = $this->validar();
            if (!empty($erros)) {
                throw new Exception('Dados inválidos: ' . implode(', ', $erros));
            }
            
            if ($this->id) {
                // Atualizar retirada existente
                $sql = "UPDATE retiradas_diretas SET 
                        produto_id = :produto_id,
                        quantidade_retirada = :quantidade_retirada,
                        destino = :destino,
                        responsavel = :responsavel,
                        observacoes = :observacoes
                        WHERE id = :id";
                
                $params = [
                    ':id' => $this->id,
                    ':produto_id' => $this->produtoId,
                    ':quantidade_retirada' => $this->quantidadeRetirada,
                    ':destino' => $this->destino,
                    ':responsavel' => $this->responsavel,
                    ':observacoes' => $this->observacoes
                ];
            } else {
                // Inserir nova retirada
                $sql = "INSERT INTO retiradas_diretas (produto_id, quantidade_retirada, destino, responsavel, data_retirada, observacoes)
                        VALUES (:produto_id, :quantidade_retirada, :destino, :responsavel, :data_retirada, :observacoes)";
                
                $params = [
                    ':produto_id' => $this->produtoId,
                    ':quantidade_retirada' => $this->quantidadeRetirada,
                    ':destino' => $this->destino,
                    ':responsavel' => $this->responsavel,
                    ':data_retirada' => $this->dataRetirada->format('Y-m-d H:i:s'),
                    ':observacoes' => $this->observacoes
                ];
            }
            
            $this->db->query($sql, $params);
            
            if (!$this->id) {
                $this->id = $this->db->getLastInsertId();
            }
            
            return true;
        } catch (Exception $e) {
            debugLog("Erro ao salvar retirada direta: " . $e->getMessage(), [
                'produto_id' => $this->produtoId,
                'quantidade' => $this->quantidadeRetirada
            ]);
            return false;
        }
    }
    
    /**
     * Busca retirada por ID
     */
    public static function buscarPorId($id) {
        try {
            $db = Database::getInstance();
            $sql = "SELECT * FROM retiradas_diretas WHERE id = :id";
            $result = $db->fetchOne($sql, [':id' => $id]);
            
            if ($result) {
                $retirada = new self();
                $retirada->id = $result['id'];
                $retirada->produtoId = $result['produto_id'];
                $retirada->quantidadeRetirada = $result['quantidade_retirada'];
                $retirada->destino = $result['destino'];
                $retirada->responsavel = $result['responsavel'];
                $retirada->dataRetirada = new DateTime($result['data_retirada']);
                $retirada->observacoes = $result['observacoes'];
                
                return $retirada;
            }
            
            return null;
        } catch (Exception $e) {
            debugLog("Erro ao buscar retirada direta: " . $e->getMessage(), ['id' => $id]);
            return null;
        }
    }
    
    /**
     * Lista todas as retiradas
     */
    public static function listar($produtoId = null, $limite = null) {
        try {
            $db = Database::getInstance();
            
            $sql = "SELECT rd.*, p.nome as produto_nome, p.unidade_medida 
                    FROM retiradas_diretas rd
                    LEFT JOIN produtos p ON rd.produto_id = p.id";
            
            $params = [];
            
            if ($produtoId) {
                $sql .= " WHERE rd.produto_id = :produto_id";
                $params[':produto_id'] = $produtoId;
            }
            
            $sql .= " ORDER BY rd.data_retirada DESC";
            
            if ($limite) {
                $sql .= " LIMIT :limite";
                $params[':limite'] = $limite;
            }
            
            $results = $db->fetchAll($sql, $params);
            $retiradas = [];
            
            foreach ($results as $result) {
                $retirada = new self();
                $retirada->id = $result['id'];
                $retirada->produtoId = $result['produto_id'];
                $retirada->quantidadeRetirada = $result['quantidade_retirada'];
                $retirada->destino = $result['destino'];
                $retirada->responsavel = $result['responsavel'];
                $retirada->dataRetirada = new DateTime($result['data_retirada']);
                $retirada->observacoes = $result['observacoes'];
                
                // Adicionar informações do produto
                $retirada->produtoNome = $result['produto_nome'];
                $retirada->unidadeMedida = $result['unidade_medida'];
                
                $retiradas[] = $retirada;
            }
            
            return $retiradas;
        } catch (Exception $e) {
            debugLog("Erro ao listar retiradas diretas: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Exclui a retirada
     */
    public function excluir() {
        try {
            if (!$this->id) {
                throw new Exception('ID da retirada não definido');
            }
            
            $sql = "DELETE FROM retiradas_diretas WHERE id = :id";
            $this->db->query($sql, [':id' => $this->id]);
            
            return true;
        } catch (Exception $e) {
            debugLog("Erro ao excluir retirada direta: " . $e->getMessage(), ['id' => $this->id]);
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
    
    /**
     * Calcula o valor da retirada baseado no preço médio
     */
    public function getValorRetirada() {
        try {
            $sql = "SELECT AVG(ed.preco_unitario) as preco_medio
                    FROM entradas_diretas ed
                    WHERE ed.produto_id = :produto_id";
            
            $result = $this->db->fetchOne($sql, [':produto_id' => $this->produtoId]);
            $precoMedio = $result ? (float)$result['preco_medio'] : 0;
            
            return $this->quantidadeRetirada * $precoMedio;
        } catch (Exception $e) {
            return 0;
        }
    }
}
?>

