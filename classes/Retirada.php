<?php
/**
 * Classe Retirada
 * Sistema de Controle de Estoque - Pizzaria
 */

class Retirada {
    private $id;
    private $producaoId;
    private $quantidadeRetirada;
    private $destino;
    private $responsavel;
    private $dataRetirada;
    private $observacoes;
    private $db;
    
    // Propriedades públicas para dados relacionados (evita erro PHP 8+)
    public $produtoNome;
    public $producaoInfo;
    public $custoPorPorcao;
    
    /**
     * Construtor
     */
    public function __construct($producaoId = null, $quantidadeRetirada = null, $destino = null) {
        $this->db = Database::getInstance();
        $this->dataRetirada = new DateTime(); // Sempre inicializar a data
        
        if ($producaoId !== null && $quantidadeRetirada !== null && $destino !== null) {
            $this->producaoId = $producaoId;
            $this->quantidadeRetirada = (int)$quantidadeRetirada;
            $this->destino = $destino;
        }
    }
    
    // Getters
    public function getId() { return $this->id; }
    public function getProducaoId() { return $this->producaoId; }
    public function getQuantidadeRetirada() { return $this->quantidadeRetirada; }
    public function getDestino() { return $this->destino; }
    public function getResponsavel() { return $this->responsavel; }
    public function getDataRetirada() { return $this->dataRetirada; }
    public function getObservacoes() { return $this->observacoes; }
    
    // Setters
    public function setId($id) { $this->id = $id; }
    public function setProducaoId($producaoId) { $this->producaoId = $producaoId; }
    public function setQuantidadeRetirada($quantidadeRetirada) { $this->quantidadeRetirada = (int)$quantidadeRetirada; }
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
     * Calcula o valor total da retirada
     */
    public function calcularValor() {
        try {
            if ($this->custoPorPorcao !== null) {
                return $this->quantidadeRetirada * $this->custoPorPorcao;
            }
            
            $producao = Producao::buscarPorId($this->producaoId);
            if ($producao) {
                return $this->quantidadeRetirada * $producao->getCustoPorPorcao();
            }
            
            return 0;
        } catch (Exception $e) {
            debugLog("Erro ao calcular valor da retirada: " . $e->getMessage(), $this);
            return 0;
        }
    }
    
    /**
     * Valida os dados da retirada
     */
    public function validar() {
        $erros = [];
        
        if (empty($this->producaoId)) {
            $erros[] = 'Produção é obrigatória';
        }
        
        if (empty($this->quantidadeRetirada) || $this->quantidadeRetirada <= 0) {
            $erros[] = 'Quantidade retirada deve ser maior que zero';
        }
        
        if (empty($this->destino)) {
            $erros[] = 'Destino é obrigatório';
        }
        
        if (empty($this->dataRetirada)) {
            $erros[] = 'Data de retirada é obrigatória';
        }
        
        // Verificar se há estoque suficiente na produção
        if ($this->producaoId && $this->quantidadeRetirada > 0) {
            $producao = Producao::buscarPorId($this->producaoId);
            if ($producao) {
                $quantidadeDisponivel = $producao->calcularQuantidadeDisponivel();
                if ($this->quantidadeRetirada > $quantidadeDisponivel) {
                    $erros[] = 'Quantidade insuficiente na produção. Disponível: ' . $quantidadeDisponivel . ' porções';
                }
            }
        }
        
        return $erros;
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
            
            // Garantir que dataRetirada está definida
            if (!$this->dataRetirada) {
                $this->dataRetirada = new DateTime();
            }
            
            if ($this->id) {
                // Atualizar retirada existente
                $sql = "UPDATE retiradas SET 
                        producao_id = :producao_id,
                        quantidade_retirada = :quantidade_retirada,
                        destino = :destino,
                        responsavel = :responsavel,
                        data_retirada = :data_retirada,
                        observacoes = :observacoes
                        WHERE id = :id";
                
                $params = [
                    ':id' => $this->id,
                    ':producao_id' => $this->producaoId,
                    ':quantidade_retirada' => $this->quantidadeRetirada,
                    ':destino' => $this->destino,
                    ':responsavel' => $this->responsavel,
                    ':data_retirada' => $this->dataRetirada->format('Y-m-d H:i:s'),
                    ':observacoes' => $this->observacoes
                ];
            } else {
                // Inserir nova retirada
                $sql = "INSERT INTO retiradas (producao_id, quantidade_retirada, destino, responsavel, data_retirada, observacoes)
                        VALUES (:producao_id, :quantidade_retirada, :destino, :responsavel, :data_retirada, :observacoes)";
                
                $params = [
                    ':producao_id' => $this->producaoId,
                    ':quantidade_retirada' => $this->quantidadeRetirada,
                    ':destino' => $this->destino,
                    ':responsavel' => $this->responsavel,
                    ':data_retirada' => $this->dataRetirada->format('Y-m-d H:i:s'),
                    ':observacoes' => $this->observacoes
                ];
            }
            
            $this->db->query($sql, $params);
            
            if (!$this->id) {
                $this->id = $this->db->lastInsertId();
            }
            
            return true;
        } catch (Exception $e) {
            debugLog("Erro ao salvar retirada: " . $e->getMessage(), $this);
            return false;
        }
    }
    
    /**
     * Busca retirada por ID
     */
    public static function buscarPorId($id) {
        try {
            $db = Database::getInstance();
            $sql = "SELECT r.*, p.nome as produto_nome, pr.custo_por_porcao
                    FROM retiradas r 
                    LEFT JOIN producao pr ON r.producao_id = pr.id
                    LEFT JOIN lotes l ON pr.lote_id = l.id
                    LEFT JOIN produtos p ON l.produto_id = p.id
                    WHERE r.id = :id";
            
            $result = $db->fetchOne($sql, [':id' => $id]);
            
            if ($result) {
                $retirada = new self();
                $retirada->id = $result['id'];
                $retirada->producaoId = $result['producao_id'];
                $retirada->quantidadeRetirada = (int)$result['quantidade_retirada'];
                $retirada->destino = $result['destino'];
                $retirada->responsavel = $result['responsavel'];
                $retirada->dataRetirada = new DateTime($result['data_retirada']);
                $retirada->observacoes = $result['observacoes'];
                $retirada->produtoNome = $result['produto_nome'];
                $retirada->custoPorPorcao = (float)$result['custo_por_porcao'];
                
                return $retirada;
            }
            
            return null;
        } catch (Exception $e) {
            debugLog("Erro ao buscar retirada: " . $e->getMessage(), ['id' => $id]);
            return null;
        }
    }
    
    /**
     * Lista todas as retiradas
     */
    public static function listarTodas() {
        try {
            $db = Database::getInstance();
            $sql = "SELECT r.*, p.nome as produto_nome, pr.custo_por_porcao
                    FROM retiradas r 
                    LEFT JOIN producao pr ON r.producao_id = pr.id
                    LEFT JOIN lotes l ON pr.lote_id = l.id
                    LEFT JOIN produtos p ON l.produto_id = p.id
                    ORDER BY r.data_retirada DESC";
            
            $results = $db->query($sql);
            $retiradas = [];
            
            foreach ($results as $row) {
                $retirada = new self();
                $retirada->id = $row['id'];
                $retirada->producaoId = $row['producao_id'];
                $retirada->quantidadeRetirada = (int)$row['quantidade_retirada'];
                $retirada->destino = $row['destino'];
                $retirada->responsavel = $row['responsavel'];
                $retirada->dataRetirada = new DateTime($row['data_retirada']);
                $retirada->observacoes = $row['observacoes'];
                $retirada->produtoNome = $row['produto_nome'];
                $retirada->custoPorPorcao = (float)$row['custo_por_porcao'];
                
                $retiradas[] = $retirada;
            }
            
            return $retiradas;
        } catch (Exception $e) {
            debugLog("Erro ao listar retiradas: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Lista retiradas por produção
     */
    public static function listarPorProducao($producaoId) {
        try {
            $db = Database::getInstance();
            $sql = "SELECT r.*, p.nome as produto_nome, pr.custo_por_porcao
                    FROM retiradas r 
                    LEFT JOIN producao pr ON r.producao_id = pr.id
                    LEFT JOIN lotes l ON pr.lote_id = l.id
                    LEFT JOIN produtos p ON l.produto_id = p.id
                    WHERE r.producao_id = :producao_id
                    ORDER BY r.data_retirada DESC";
            
            $results = $db->query($sql, [':producao_id' => $producaoId]);
            $retiradas = [];
            
            foreach ($results as $row) {
                $retirada = new self();
                $retirada->id = $row['id'];
                $retirada->producaoId = $row['producao_id'];
                $retirada->quantidadeRetirada = (int)$row['quantidade_retirada'];
                $retirada->destino = $row['destino'];
                $retirada->responsavel = $row['responsavel'];
                $retirada->dataRetirada = new DateTime($row['data_retirada']);
                $retirada->observacoes = $row['observacoes'];
                $retirada->produtoNome = $row['produto_nome'];
                $retirada->custoPorPorcao = (float)$row['custo_por_porcao'];
                
                $retiradas[] = $retirada;
            }
            
            return $retiradas;
        } catch (Exception $e) {
            debugLog("Erro ao listar retiradas por produção: " . $e->getMessage(), ['producao_id' => $producaoId]);
            return [];
        }
    }
    
    /**
     * Lista destinos únicos
     */
    public static function listarDestinos() {
        try {
            $db = Database::getInstance();
            $sql = "SELECT DISTINCT destino FROM retiradas ORDER BY destino";
            
            $results = $db->query($sql);
            $destinos = [];
            
            foreach ($results as $row) {
                $destinos[] = $row['destino'];
            }
            
            return $destinos;
        } catch (Exception $e) {
            debugLog("Erro ao listar destinos: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Exclui a retirada
     */
    public function excluir() {
        try {
            if (!$this->id) {
                throw new Exception('Retirada não foi salva ainda');
            }
            
            $sql = "DELETE FROM retiradas WHERE id = :id";
            $this->db->query($sql, [':id' => $this->id]);
            
            return true;
        } catch (Exception $e) {
            debugLog("Erro ao excluir retirada: " . $e->getMessage(), $this);
            return false;
        }
    }
}
?>

