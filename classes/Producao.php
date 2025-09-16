<?php
/**
 * Classe Producao
 * Sistema de Controle de Estoque - Pizzaria
 */

class Producao {
    private $id;
    private $loteId;
    private $quantidadeProduzida;
    private $quantidadeMateriaPrimaUsada;
    private $custoTotalProducao;
    private $custoItensExtras;
    private $custoPorPorcao;
    private $dataProducao;
    private $observacoes;
    private $db;
    
    // Propriedades públicas para dados relacionados (evita erro PHP 8+)
    public $produtoNome;
    public $loteInfo;
    public $quantidadeDisponivel;
    public $totalRetiradas;
    
    /**
     * Construtor
     */
    public function __construct($loteId = null, $quantidadeProduzida = null, $quantidadeMateriaPrimaUsada = null) {
        $this->db = Database::getInstance();
        $this->dataProducao = new DateTime(); // Sempre inicializar a data
        
        if ($loteId !== null && $quantidadeProduzida !== null && $quantidadeMateriaPrimaUsada !== null) {
            $this->loteId = $loteId;
            $this->quantidadeProduzida = (int)$quantidadeProduzida;
            $this->quantidadeMateriaPrimaUsada = (float)$quantidadeMateriaPrimaUsada;
            $this->calcularCustos();
        }
    }
    
    // Getters
    public function getId() { return $this->id; }
    public function getLoteId() { return $this->loteId; }
    public function getQuantidadeProduzida() { return $this->quantidadeProduzida; }
    public function getQuantidadeMateriaPrimaUsada() { return $this->quantidadeMateriaPrimaUsada; }
    public function getCustoTotalProducao() { return $this->custoTotalProducao; }
    public function getCustoItensExtras() { return $this->custoItensExtras; }
    public function getCustoPorPorcao() { return $this->custoPorPorcao; }
    public function getDataProducao() { return $this->dataProducao; }
    public function getObservacoes() { return $this->observacoes; }
    
    // Setters
    public function setId($id) { $this->id = $id; }
    public function setLoteId($loteId) { 
        $this->loteId = $loteId; 
        $this->calcularCustos();
    }
    public function setQuantidadeProduzida($quantidadeProduzida) { 
        $this->quantidadeProduzida = (int)$quantidadeProduzida; 
        $this->calcularCustos();
    }
    public function setQuantidadeMateriaPrimaUsada($quantidadeMateriaPrimaUsada) { 
        $this->quantidadeMateriaPrimaUsada = (float)$quantidadeMateriaPrimaUsada; 
        $this->calcularCustos();
    }
    public function setDataProducao($dataProducao) {
        if ($dataProducao instanceof DateTime) {
            $this->dataProducao = $dataProducao;
        } else {
            $this->dataProducao = new DateTime($dataProducao);
        }
    }
    public function setObservacoes($observacoes) { $this->observacoes = $observacoes; }
    public function setCustoItensExtras($custoItensExtras) { 
        $this->custoItensExtras = (float)$custoItensExtras;
        $this->recalcularCustoPorPorcao();
    }
    
    /**
     * Calcula os custos da produção
     */
    private function calcularCustos() {
        try {
            if ($this->loteId && $this->quantidadeMateriaPrimaUsada > 0 && $this->quantidadeProduzida > 0) {
                // Buscar custo por unidade do lote
                $lote = Lote::buscarPorId($this->loteId);
                if ($lote) {
                    $this->custoTotalProducao = $this->quantidadeMateriaPrimaUsada * $lote->getCustoPorUnidade();
                    $this->recalcularCustoPorPorcao();
                } else {
                    $this->custoTotalProducao = 0;
                    $this->custoPorPorcao = 0;
                }
            } else {
                $this->custoTotalProducao = 0;
                $this->custoPorPorcao = 0;
            }
        } catch (Exception $e) {
            debugLog("Erro ao calcular custos da produção: " . $e->getMessage(), $this);
            $this->custoTotalProducao = 0;
            $this->custoPorPorcao = 0;
        }
    }
    
    /**
     * Recalcula o custo por porção incluindo itens extras
     */
    private function recalcularCustoPorPorcao() {
        if ($this->quantidadeProduzida > 0) {
            $custoTotal = $this->custoTotalProducao + ($this->custoItensExtras ?? 0);
            $this->custoPorPorcao = $custoTotal / $this->quantidadeProduzida;
        } else {
            $this->custoPorPorcao = 0;
        }
    }
    
    /**
     * Valida os dados da produção
     */
    public function validar() {
        $erros = [];
        
        if (empty($this->loteId)) {
            $erros[] = 'Lote é obrigatório';
        }
        
        if (empty($this->quantidadeProduzida) || $this->quantidadeProduzida <= 0) {
            $erros[] = 'Quantidade produzida deve ser maior que zero';
        }
        
        if (empty($this->quantidadeMateriaPrimaUsada) || $this->quantidadeMateriaPrimaUsada <= 0) {
            $erros[] = 'Quantidade de matéria-prima usada deve ser maior que zero';
        }
        
        if (empty($this->dataProducao)) {
            $erros[] = 'Data de produção é obrigatória';
        }
        
        // Verificar se há estoque suficiente no lote
        if ($this->loteId && $this->quantidadeMateriaPrimaUsada > 0) {
            $lote = Lote::buscarPorId($this->loteId);
            if ($lote) {
                $quantidadeRestante = $lote->calcularQuantidadeRestante();
                if ($this->quantidadeMateriaPrimaUsada > $quantidadeRestante) {
                    $erros[] = 'Quantidade de matéria-prima insuficiente no lote. Disponível: ' . formatNumber($quantidadeRestante);
                }
            }
        }
        
        return $erros;
    }
    
    /**
     * Salva a produção no banco de dados
     */
    public function salvar() {
        try {
            $erros = $this->validar();
            if (!empty($erros)) {
                throw new Exception('Dados inválidos: ' . implode(', ', $erros));
            }
            
            // Garantir que dataProducao está definida
            if (!$this->dataProducao) {
                $this->dataProducao = new DateTime();
            }
            
            // Recalcular custos
            $this->calcularCustos();
            
            if ($this->id) {
                // Atualizar produção existente
                $sql = "UPDATE producao SET 
                        lote_id = :lote_id,
                        quantidade_produzida = :quantidade_produzida,
                        quantidade_materia_prima_usada = :quantidade_materia_prima_usada,
                        custo_total_producao = :custo_total_producao,
                        custo_itens_extras = :custo_itens_extras,
                        custo_por_porcao = :custo_por_porcao,
                        data_producao = :data_producao,
                        observacoes = :observacoes
                        WHERE id = :id";
                
                $params = [
                    ':id' => $this->id,
                    ':lote_id' => $this->loteId,
                    ':quantidade_produzida' => $this->quantidadeProduzida,
                    ':quantidade_materia_prima_usada' => $this->quantidadeMateriaPrimaUsada,
                    ':custo_total_producao' => $this->custoTotalProducao,
                    ':custo_itens_extras' => $this->custoItensExtras ?? 0,
                    ':custo_por_porcao' => $this->custoPorPorcao,
                    ':data_producao' => $this->dataProducao->format('Y-m-d H:i:s'),
                    ':observacoes' => $this->observacoes
                ];
            } else {
                // Inserir nova produção
                $sql = "INSERT INTO producao (lote_id, quantidade_produzida, quantidade_materia_prima_usada, custo_total_producao, custo_itens_extras, custo_por_porcao, data_producao, observacoes)
                        VALUES (:lote_id, :quantidade_produzida, :quantidade_materia_prima_usada, :custo_total_producao, :custo_itens_extras, :custo_por_porcao, :data_producao, :observacoes)";
                
                $params = [
                    ':lote_id' => $this->loteId,
                    ':quantidade_produzida' => $this->quantidadeProduzida,
                    ':quantidade_materia_prima_usada' => $this->quantidadeMateriaPrimaUsada,
                    ':custo_total_producao' => $this->custoTotalProducao,
                    ':custo_itens_extras' => $this->custoItensExtras ?? 0,
                    ':custo_por_porcao' => $this->custoPorPorcao,
                    ':data_producao' => $this->dataProducao->format('Y-m-d H:i:s'),
                    ':observacoes' => $this->observacoes
                ];
            }
            
            $this->db->query($sql, $params);
            
            if (!$this->id) {
                $this->id = $this->db->lastInsertId();
            }
            
            return true;
        } catch (Exception $e) {
            debugLog("Erro ao salvar produção: " . $e->getMessage(), $this);
            return false;
        }
    }
    
    /**
     * Busca produção por ID
     */
    public static function buscarPorId($id) {
        try {
            $db = Database::getInstance();
            $sql = "SELECT pr.*, p.nome as produto_nome, l.preco_compra, l.custo_por_unidade,
                           (pr.quantidade_produzida - COALESCE(SUM(r.quantidade_retirada), 0)) as quantidade_disponivel
                    FROM producao pr
                    LEFT JOIN lotes l ON pr.lote_id = l.id
                    LEFT JOIN produtos p ON l.produto_id = p.id
                    LEFT JOIN retiradas r ON pr.id = r.producao_id
                    WHERE pr.id = :id
                    GROUP BY pr.id";
            
            $result = $db->fetchOne($sql, [':id' => $id]);
            
            if ($result) {
                $producao = new self();
                $producao->id = $result['id'];
                $producao->loteId = $result['lote_id'];
                $producao->quantidadeProduzida = (int)$result['quantidade_produzida'];
                $producao->quantidadeMateriaPrimaUsada = (float)$result['quantidade_materia_prima_usada'];
                $producao->custoTotalProducao = (float)$result['custo_total_producao'];
                $producao->custoPorPorcao = (float)$result['custo_por_porcao'];
                $producao->dataProducao = new DateTime($result['data_producao']);
                $producao->observacoes = $result['observacoes'];
                $producao->produtoNome = $result['produto_nome'];
                $producao->quantidadeDisponivel = (int)$result['quantidade_disponivel'];
                
                return $producao;
            }
            
            return null;
        } catch (Exception $e) {
            debugLog("Erro ao buscar produção: " . $e->getMessage(), ['id' => $id]);
            return null;
        }
    }
    
    /**
     * Lista todas as produções
     */
    public static function listarTodas() {
        try {
            $db = Database::getInstance();
            $sql = "SELECT pr.*, p.nome as produto_nome,
                           (pr.quantidade_produzida - COALESCE(SUM(r.quantidade_retirada), 0)) as quantidade_disponivel,
                           COUNT(r.id) as total_retiradas
                    FROM producao pr
                    LEFT JOIN lotes l ON pr.lote_id = l.id
                    LEFT JOIN produtos p ON l.produto_id = p.id
                    LEFT JOIN retiradas r ON pr.id = r.producao_id
                    GROUP BY pr.id
                    ORDER BY pr.data_producao DESC";
            
            $results = $db->query($sql);
            $producoes = [];
            
            foreach ($results as $row) {
                $producao = new self();
                $producao->id = $row['id'];
                $producao->loteId = $row['lote_id'];
                $producao->quantidadeProduzida = (int)$row['quantidade_produzida'];
                $producao->quantidadeMateriaPrimaUsada = (float)$row['quantidade_materia_prima_usada'];
                $producao->custoTotalProducao = (float)$row['custo_total_producao'];
                $producao->custoPorPorcao = (float)$row['custo_por_porcao'];
                $producao->dataProducao = new DateTime($row['data_producao']);
                $producao->observacoes = $row['observacoes'];
                $producao->produtoNome = $row['produto_nome'];
                $producao->quantidadeDisponivel = (int)$row['quantidade_disponivel'];
                $producao->totalRetiradas = (int)$row['total_retiradas'];
                
                $producoes[] = $producao;
            }
            
            return $producoes;
        } catch (Exception $e) {
            debugLog("Erro ao listar produções: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Lista produções por lote
     */
    public static function listarPorLote($loteId) {
        try {
            $db = Database::getInstance();
            $sql = "SELECT pr.*, p.nome as produto_nome,
                           (pr.quantidade_produzida - COALESCE(SUM(r.quantidade_retirada), 0)) as quantidade_disponivel
                    FROM producao pr
                    LEFT JOIN lotes l ON pr.lote_id = l.id
                    LEFT JOIN produtos p ON l.produto_id = p.id
                    LEFT JOIN retiradas r ON pr.id = r.producao_id
                    WHERE pr.lote_id = :lote_id
                    GROUP BY pr.id
                    ORDER BY pr.data_producao DESC";
            
            $results = $db->query($sql, [':lote_id' => $loteId]);
            $producoes = [];
            
            foreach ($results as $row) {
                $producao = new self();
                $producao->id = $row['id'];
                $producao->loteId = $row['lote_id'];
                $producao->quantidadeProduzida = (int)$row['quantidade_produzida'];
                $producao->quantidadeMateriaPrimaUsada = (float)$row['quantidade_materia_prima_usada'];
                $producao->custoTotalProducao = (float)$row['custo_total_producao'];
                $producao->custoPorPorcao = (float)$row['custo_por_porcao'];
                $producao->dataProducao = new DateTime($row['data_producao']);
                $producao->observacoes = $row['observacoes'];
                $producao->produtoNome = $row['produto_nome'];
                $producao->quantidadeDisponivel = (int)$row['quantidade_disponivel'];
                
                $producoes[] = $producao;
            }
            
            return $producoes;
        } catch (Exception $e) {
            debugLog("Erro ao listar produções por lote: " . $e->getMessage(), ['lote_id' => $loteId]);
            return [];
        }
    }
    
    /**
     * Lista produções disponíveis (com estoque)
     */
    public static function listarDisponiveis() {
        try {
            $db = Database::getInstance();
            $sql = "SELECT pr.*, p.nome as produto_nome, l.id as lote_id,
                           (pr.quantidade_produzida - COALESCE(SUM(r.quantidade_retirada), 0)) as quantidade_disponivel
                    FROM producao pr
                    INNER JOIN lotes l ON pr.lote_id = l.id
                    INNER JOIN produtos p ON l.produto_id = p.id
                    LEFT JOIN retiradas r ON pr.id = r.producao_id
                    GROUP BY pr.id, pr.lote_id, pr.quantidade_produzida, pr.quantidade_materia_prima_usada, 
                             pr.custo_total_producao, pr.custo_por_porcao, pr.data_producao, pr.observacoes, 
                             p.nome, l.id
                    HAVING quantidade_disponivel > 0
                    ORDER BY pr.data_producao ASC"; // FIFO
            
            $results = $db->query($sql);
            $producoes = [];
            
            foreach ($results as $row) {
                $producao = new self();
                $producao->id = $row['id'];
                $producao->loteId = $row['lote_id'];
                $producao->quantidadeProduzida = (int)$row['quantidade_produzida'];
                $producao->quantidadeMateriaPrimaUsada = (float)$row['quantidade_materia_prima_usada'];
                $producao->custoTotalProducao = (float)$row['custo_total_producao'];
                $producao->custoPorPorcao = (float)$row['custo_por_porcao'];
                $producao->dataProducao = new DateTime($row['data_producao']);
                $producao->observacoes = $row['observacoes'];
                $producao->produtoNome = $row['produto_nome'];
                $producao->quantidadeDisponivel = (int)$row['quantidade_disponivel'];
                
                $producoes[] = $producao;
            }
            
            return $producoes;
        } catch (Exception $e) {
            debugLog("Erro ao listar produções disponíveis: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Calcula quantidade disponível da produção
     */
    public function calcularQuantidadeDisponivel() {
        try {
            if (!$this->id) {
                return $this->quantidadeProduzida;
            }
            
            $sql = "SELECT COALESCE(SUM(quantidade_retirada), 0) as total_retirado
                    FROM retiradas 
                    WHERE producao_id = :producao_id";
            
            $result = $this->db->fetchOne($sql, [':producao_id' => $this->id]);
            $totalRetirado = (int)$result['total_retirado'];
            
            return $this->quantidadeProduzida - $totalRetirado;
        } catch (Exception $e) {
            debugLog("Erro ao calcular quantidade disponível: " . $e->getMessage(), $this);
            return 0;
        }
    }
    
    /**
     * Verifica se a produção pode ser excluída
     */
    public function podeExcluir() {
        try {
            if (!$this->id) {
                return false;
            }
            
            // Verifica se há retiradas vinculadas
            $sql = "SELECT COUNT(*) as total FROM retiradas WHERE producao_id = :producao_id";
            $result = $this->db->fetchOne($sql, [':producao_id' => $this->id]);
            
            return $result['total'] == 0;
        } catch (Exception $e) {
            debugLog("Erro ao verificar se produção pode ser excluída: " . $e->getMessage(), $this);
            return false;
        }
    }
    
    /**
     * Exclui a produção permanentemente
     */
    public function excluir() {
        try {
            if (!$this->id) {
                throw new Exception('Produção não foi salva ainda');
            }
            
            if (!$this->podeExcluir()) {
                throw new Exception('Produção não pode ser excluída pois possui retiradas vinculadas');
            }
            
            $sql = "DELETE FROM producao WHERE id = :id";
            $this->db->query($sql, [':id' => $this->id]);
            
            return true;
        } catch (Exception $e) {
            debugLog("Erro ao excluir produção: " . $e->getMessage(), $this);
            return false;
        }
    }
    
    /**
     * Retorna o estoque disponível da produção (alias para calcularQuantidadeDisponivel)
     */
    public function getEstoqueDisponivel() {
        return $this->calcularQuantidadeDisponivel();
    }
    
    /**
     * Retorna o lote associado à produção
     */
    public function getLote() {
        try {
            if (!$this->loteId) {
                return null;
            }
            
            return Lote::buscarPorId($this->loteId);
        } catch (Exception $e) {
            debugLog("Erro ao buscar lote da produção: " . $e->getMessage(), $this);
            return null;
        }
    }
    
    /**
     * Retorna o total de retiradas da produção
     */
    public function getTotalRetiradas() {
        try {
            if (!$this->id) {
                return 0;
            }
            
            $sql = "SELECT COUNT(*) as total FROM retiradas WHERE producao_id = :producao_id";
            $result = $this->db->fetchOne($sql, [':producao_id' => $this->id]);
            
            return (int)$result['total'];
        } catch (Exception $e) {
            debugLog("Erro ao contar retiradas da produção: " . $e->getMessage(), $this);
            return 0;
        }
    }
}
?>

