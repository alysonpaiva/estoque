<?php
/**
 * Classe Lote
 * Sistema de Controle de Estoque - Pizzaria
 */

class Lote {
    private $id;
    private $produtoId;
    private $precoCompra;
    private $quantidadeComprada;
    private $custoPorUnidade;
    private $dataCompra;
    private $observacoes;
    private $db;
    
    // Propriedades públicas para dados relacionados (evita erro PHP 8+)
    public $produtoNome;
    public $quantidadeRestante;
    public $totalProducoes;
    
    /**
     * Construtor
     */
    public function __construct($produtoId = null, $precoCompra = null, $quantidadeComprada = null, $dataCompra = null) {
        $this->db = Database::getInstance();
        
        if ($produtoId !== null && $precoCompra !== null && $quantidadeComprada !== null) {
            $this->produtoId = $produtoId;
            $this->precoCompra = (float)$precoCompra;
            $this->quantidadeComprada = (float)$quantidadeComprada;
            $this->dataCompra = $dataCompra ? new DateTime($dataCompra) : new DateTime();
            $this->calcularCustoPorUnidade();
        }
    }
    
    // Getters
    public function getId() { return $this->id; }
    public function getProdutoId() { return $this->produtoId; }
    public function getPrecoCompra() { return $this->precoCompra; }
    public function getQuantidadeComprada() { return $this->quantidadeComprada; }
    public function getCustoPorUnidade() { return $this->custoPorUnidade; }
    public function getDataCompra() { return $this->dataCompra; }
    public function getObservacoes() { return $this->observacoes; }
    
    // Setters
    public function setId($id) { $this->id = $id; }
    public function setProdutoId($produtoId) { 
        $this->produtoId = $produtoId; 
        $this->calcularCustoPorUnidade();
    }
    public function setPrecoCompra($precoCompra) { 
        $this->precoCompra = (float)$precoCompra; 
        $this->calcularCustoPorUnidade();
    }
    public function setQuantidadeComprada($quantidadeComprada) { 
        $this->quantidadeComprada = (float)$quantidadeComprada; 
        $this->calcularCustoPorUnidade();
    }
    public function setDataCompra($dataCompra) {
        if ($dataCompra instanceof DateTime) {
            $this->dataCompra = $dataCompra;
        } else {
            $this->dataCompra = new DateTime($dataCompra);
        }
    }
    public function setObservacoes($observacoes) { $this->observacoes = $observacoes; }
    
    /**
     * Calcula o custo por unidade
     */
    private function calcularCustoPorUnidade() {
        if ($this->precoCompra > 0 && $this->quantidadeComprada > 0) {
            $this->custoPorUnidade = $this->precoCompra / $this->quantidadeComprada;
        } else {
            $this->custoPorUnidade = 0;
        }
    }
    
    /**
     * Valida os dados do lote
     */
    public function validar() {
        $erros = [];
        
        if (empty($this->produtoId)) {
            $erros[] = 'Produto é obrigatório';
        }
        
        if (empty($this->precoCompra) || $this->precoCompra <= 0) {
            $erros[] = 'Preço de compra deve ser maior que zero';
        }
        
        if (empty($this->quantidadeComprada) || $this->quantidadeComprada <= 0) {
            $erros[] = 'Quantidade comprada deve ser maior que zero';
        }
        
        if (empty($this->dataCompra)) {
            $erros[] = 'Data de compra é obrigatória';
        }
        
        return $erros;
    }
    
    /**
     * Salva o lote no banco de dados
     */
    public function salvar() {
        try {
            $erros = $this->validar();
            if (!empty($erros)) {
                throw new Exception('Dados inválidos: ' . implode(', ', $erros));
            }
            
            // Garantir que dataCompra está definida
            if (!$this->dataCompra) {
                $this->dataCompra = new DateTime();
            }
            
            // Recalcular custo por unidade
            $this->calcularCustoPorUnidade();
            
            if ($this->id) {
                // Atualizar lote existente
                $sql = "UPDATE lotes SET 
                        produto_id = :produto_id,
                        preco_compra = :preco_compra,
                        quantidade_comprada = :quantidade_comprada,
                        custo_por_unidade = :custo_por_unidade,
                        data_compra = :data_compra,
                        observacoes = :observacoes
                        WHERE id = :id";
                
                $params = [
                    ':id' => $this->id,
                    ':produto_id' => $this->produtoId,
                    ':preco_compra' => $this->precoCompra,
                    ':quantidade_comprada' => $this->quantidadeComprada,
                    ':custo_por_unidade' => $this->custoPorUnidade,
                    ':data_compra' => $this->dataCompra->format('Y-m-d H:i:s'),
                    ':observacoes' => $this->observacoes
                ];
            } else {
                // Inserir novo lote
                $sql = "INSERT INTO lotes (produto_id, preco_compra, quantidade_comprada, custo_por_unidade, data_compra, observacoes)
                        VALUES (:produto_id, :preco_compra, :quantidade_comprada, :custo_por_unidade, :data_compra, :observacoes)";
                
                $params = [
                    ':produto_id' => $this->produtoId,
                    ':preco_compra' => $this->precoCompra,
                    ':quantidade_comprada' => $this->quantidadeComprada,
                    ':custo_por_unidade' => $this->custoPorUnidade,
                    ':data_compra' => $this->dataCompra->format('Y-m-d H:i:s'),
                    ':observacoes' => $this->observacoes
                ];
            }
            
            $this->db->query($sql, $params);
            
            if (!$this->id) {
                $this->id = $this->db->lastInsertId();
            }
            
            return true;
        } catch (Exception $e) {
            debugLog("Erro ao salvar lote: " . $e->getMessage(), $this);
            return false;
        }
    }
    
    /**
     * Busca lote por ID
     */
    public static function buscarPorId($id) {
        try {
            $db = Database::getInstance();
            $sql = "SELECT l.*, p.nome as produto_nome 
                    FROM lotes l 
                    LEFT JOIN produtos p ON l.produto_id = p.id 
                    WHERE l.id = :id";
            
            $result = $db->fetchOne($sql, [':id' => $id]);
            
            if ($result) {
                $lote = new self();
                $lote->id = $result['id'];
                $lote->produtoId = $result['produto_id'];
                $lote->precoCompra = (float)$result['preco_compra'];
                $lote->quantidadeComprada = (float)$result['quantidade_comprada'];
                $lote->custoPorUnidade = (float)$result['custo_por_unidade'];
                $lote->dataCompra = new DateTime($result['data_compra']);
                $lote->observacoes = $result['observacoes'];
                $lote->produtoNome = $result['produto_nome'];
                
                return $lote;
            }
            
            return null;
        } catch (Exception $e) {
            debugLog("Erro ao buscar lote: " . $e->getMessage(), ['id' => $id]);
            return null;
        }
    }
    
    /**
     * Lista todos os lotes
     */
    public static function listarTodos() {
        try {
            $db = Database::getInstance();
            $sql = "SELECT l.*, p.nome as produto_nome,
                           (l.quantidade_comprada - COALESCE(SUM(pr.quantidade_materia_prima_usada), 0)) as quantidade_restante,
                           COUNT(pr.id) as total_producoes
                    FROM lotes l 
                    LEFT JOIN produtos p ON l.produto_id = p.id
                    LEFT JOIN producao pr ON l.id = pr.lote_id
                    GROUP BY l.id
                    ORDER BY l.data_compra DESC";
            
            $results = $db->query($sql);
            $lotes = [];
            
            foreach ($results as $row) {
                $lote = new self();
                $lote->id = $row['id'];
                $lote->produtoId = $row['produto_id'];
                $lote->precoCompra = (float)$row['preco_compra'];
                $lote->quantidadeComprada = (float)$row['quantidade_comprada'];
                $lote->custoPorUnidade = (float)$row['custo_por_unidade'];
                $lote->dataCompra = new DateTime($row['data_compra']);
                $lote->observacoes = $row['observacoes'];
                $lote->produtoNome = $row['produto_nome'];
                $lote->quantidadeRestante = (float)$row['quantidade_restante'];
                $lote->totalProducoes = (int)$row['total_producoes'];
                
                $lotes[] = $lote;
            }
            
            return $lotes;
        } catch (Exception $e) {
            debugLog("Erro ao listar lotes: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Lista lotes por produto
     */
    public static function listarPorProduto($produtoId) {
        try {
            $db = Database::getInstance();
            $sql = "SELECT l.*, p.nome as produto_nome,
                           (l.quantidade_comprada - COALESCE(SUM(pr.quantidade_materia_prima_usada), 0)) as quantidade_restante
                    FROM lotes l 
                    LEFT JOIN produtos p ON l.produto_id = p.id
                    LEFT JOIN producao pr ON l.id = pr.lote_id
                    WHERE l.produto_id = :produto_id
                    GROUP BY l.id
                    ORDER BY l.data_compra DESC";
            
            $results = $db->query($sql, [':produto_id' => $produtoId]);
            $lotes = [];
            
            foreach ($results as $row) {
                $lote = new self();
                $lote->id = $row['id'];
                $lote->produtoId = $row['produto_id'];
                $lote->precoCompra = (float)$row['preco_compra'];
                $lote->quantidadeComprada = (float)$row['quantidade_comprada'];
                $lote->custoPorUnidade = (float)$row['custo_por_unidade'];
                $lote->dataCompra = new DateTime($row['data_compra']);
                $lote->observacoes = $row['observacoes'];
                $lote->produtoNome = $row['produto_nome'];
                $lote->quantidadeRestante = (float)$row['quantidade_restante'];
                
                $lotes[] = $lote;
            }
            
            return $lotes;
        } catch (Exception $e) {
            debugLog("Erro ao listar lotes por produto: " . $e->getMessage(), ['produto_id' => $produtoId]);
            return [];
        }
    }
    
    /**
     * Lista lotes disponíveis (com estoque)
     */
    public static function listarDisponiveis($produtoId = null) {
        try {
            $db = Database::getInstance();
            $sql = "SELECT l.*, p.nome as produto_nome,
                           (l.quantidade_comprada - COALESCE(SUM(pr.quantidade_materia_prima_usada), 0)) as quantidade_restante
                    FROM lotes l 
                    LEFT JOIN produtos p ON l.produto_id = p.id
                    LEFT JOIN producao pr ON l.id = pr.lote_id
                    WHERE 1=1";
            
            $params = [];
            
            if ($produtoId) {
                $sql .= " AND l.produto_id = :produto_id";
                $params[':produto_id'] = $produtoId;
            }
            
            $sql .= " GROUP BY l.id
                     HAVING quantidade_restante > 0
                     ORDER BY l.data_compra ASC"; // FIFO - primeiro que entra, primeiro que sai
            
            $results = $db->query($sql, $params);
            $lotes = [];
            
            foreach ($results as $row) {
                $lote = new self();
                $lote->id = $row['id'];
                $lote->produtoId = $row['produto_id'];
                $lote->precoCompra = (float)$row['preco_compra'];
                $lote->quantidadeComprada = (float)$row['quantidade_comprada'];
                $lote->custoPorUnidade = (float)$row['custo_por_unidade'];
                $lote->dataCompra = new DateTime($row['data_compra']);
                $lote->observacoes = $row['observacoes'];
                $lote->produtoNome = $row['produto_nome'];
                $lote->quantidadeRestante = (float)$row['quantidade_restante'];
                
                $lotes[] = $lote;
            }
            
            return $lotes;
        } catch (Exception $e) {
            debugLog("Erro ao listar lotes disponíveis: " . $e->getMessage(), ['produto_id' => $produtoId]);
            return [];
        }
    }
    
    /**
     * Calcula quantidade restante do lote
     */
    public function calcularQuantidadeRestante() {
        try {
            if (!$this->id) {
                return $this->quantidadeComprada;
            }
            
            $sql = "SELECT COALESCE(SUM(quantidade_materia_prima_usada), 0) as total_usado
                    FROM producao 
                    WHERE lote_id = :lote_id";
            
            $result = $this->db->fetchOne($sql, [':lote_id' => $this->id]);
            $totalUsado = (float)$result['total_usado'];
            
            return $this->quantidadeComprada - $totalUsado;
        } catch (Exception $e) {
            debugLog("Erro ao calcular quantidade restante: " . $e->getMessage(), $this);
            return 0;
        }
    }
    
    /**
     * Verifica se o lote pode ser excluído
     */
    public function podeExcluir() {
        try {
            if (!$this->id) {
                return false;
            }
            
            // Verifica se há produções vinculadas
            $sql = "SELECT COUNT(*) as total FROM producao WHERE lote_id = :lote_id";
            $result = $this->db->fetchOne($sql, [':lote_id' => $this->id]);
            
            return $result['total'] == 0;
        } catch (Exception $e) {
            debugLog("Erro ao verificar se lote pode ser excluído: " . $e->getMessage(), $this);
            return false;
        }
    }
    
    /**
     * Exclui o lote permanentemente
     */
    public function excluir() {
        try {
            if (!$this->id) {
                throw new Exception('Lote não foi salvo ainda');
            }
            
            if (!$this->podeExcluir()) {
                throw new Exception('Lote não pode ser excluído pois possui produções vinculadas');
            }
            
            $sql = "DELETE FROM lotes WHERE id = :id";
            $this->db->query($sql, [':id' => $this->id]);
            
            return true;
        } catch (Exception $e) {
            debugLog("Erro ao excluir lote: " . $e->getMessage(), $this);
            return false;
        }
    }
    
    /**
     * Retorna a quantidade restante do lote (alias para calcularQuantidadeRestante)
     */
    public function getQuantidadeRestante() {
        return $this->calcularQuantidadeRestante();
    }
    
    /**
     * Retorna o produto associado ao lote
     */
    public function getProduto() {
        try {
            if (!$this->produtoId) {
                return null;
            }
            
            return Produto::buscarPorId($this->produtoId);
        } catch (Exception $e) {
            debugLog("Erro ao buscar produto do lote: " . $e->getMessage(), $this);
            return null;
        }
    }
    
    /**
     * Retorna o total de produções do lote
     */
    public function getTotalProducoes() {
        try {
            if (!$this->id) {
                return 0;
            }
            
            $sql = "SELECT COUNT(*) as total FROM producao WHERE lote_id = :lote_id";
            $result = $this->db->fetchOne($sql, [':lote_id' => $this->id]);
            
            return (int)$result['total'];
        } catch (Exception $e) {
            debugLog("Erro ao contar produções do lote: " . $e->getMessage(), $this);
            return 0;
        }
    }
    
    /**
     * Retorna informações detalhadas do lote
     */
    public function getInformacoesDetalhadas() {
        try {
            if (!$this->id) {
                return null;
            }
            
            $sql = "SELECT 
                        l.*,
                        p.nome as produto_nome,
                        p.unidade_medida,
                        COUNT(pr.id) as total_producoes,
                        COALESCE(SUM(pr.quantidade_materia_prima_usada), 0) as total_usado,
                        COALESCE(SUM(pr.quantidade_produzida), 0) as total_produzido,
                        (l.quantidade_comprada - COALESCE(SUM(pr.quantidade_materia_prima_usada), 0)) as quantidade_restante
                    FROM lotes l
                    LEFT JOIN produtos p ON l.produto_id = p.id
                    LEFT JOIN producao pr ON l.id = pr.lote_id
                    WHERE l.id = :lote_id
                    GROUP BY l.id";
            
            $result = $this->db->fetchOne($sql, [':lote_id' => $this->id]);
            
            return $result;
        } catch (Exception $e) {
            debugLog("Erro ao buscar informações detalhadas do lote: " . $e->getMessage(), $this);
            return null;
        }
    }
}
?>

