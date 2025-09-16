<?php
/**
 * Classe Venda
 * Sistema de Controle de Estoque - Pizzaria
 */

class Venda {
    private $id;
    private $semana;
    private $ano;
    private $dataInicio;
    private $dataFim;
    private $valorTotal;
    private $observacoes;
    private $dataCadastro;
    private $db;

    public function __construct($semana = null, $ano = null, $valorTotal = null) {
        $this->db = Database::getInstance();
        
        if ($semana !== null && $ano !== null && $valorTotal !== null) {
            $this->semana = (int)$semana;
            $this->ano = (int)$ano;
            $this->valorTotal = (float)$valorTotal;
            $this->calcularDatas();
            $this->dataCadastro = new DateTime();
        }
    }

    // Getters
    public function getId() { return $this->id; }
    public function getSemana() { return $this->semana; }
    public function getAno() { return $this->ano; }
    public function getDataInicio() { return $this->dataInicio; }
    public function getDataFim() { return $this->dataFim; }
    public function getValorTotal() { return $this->valorTotal; }
    public function getObservacoes() { return $this->observacoes; }
    public function getDataCadastro() { return $this->dataCadastro; }

    // Setters
    public function setSemana($semana) { 
        $this->semana = (int)$semana; 
        $this->calcularDatas();
    }
    public function setAno($ano) { 
        $this->ano = (int)$ano; 
        $this->calcularDatas();
    }
    public function setValorTotal($valorTotal) { $this->valorTotal = (float)$valorTotal; }
    public function setObservacoes($observacoes) { $this->observacoes = $observacoes; }

    /**
     * Calcular datas de início e fim da semana
     */
    private function calcularDatas() {
        if ($this->semana && $this->ano) {
            // Primeira segunda-feira do ano
            $primeiroDia = new DateTime($this->ano . '-01-01');
            $diaSemana = $primeiroDia->format('N'); // 1 = segunda, 7 = domingo
            
            if ($diaSemana > 1) {
                $primeiroDia->modify('+' . (8 - $diaSemana) . ' days');
            }
            
            // Adicionar semanas
            $primeiroDia->modify('+' . ($this->semana - 1) . ' weeks');
            
            $this->dataInicio = clone $primeiroDia;
            $this->dataFim = clone $primeiroDia;
            $this->dataFim->modify('+6 days');
        }
    }

    /**
     * Validar dados da venda
     */
    public function validar() {
        $erros = [];

        if (!$this->semana || $this->semana < 1 || $this->semana > 53) {
            $erros[] = 'Semana deve estar entre 1 e 53';
        }

        if (!$this->ano || $this->ano < 2020 || $this->ano > 2030) {
            $erros[] = 'Ano deve estar entre 2020 e 2030';
        }

        if (!$this->valorTotal || $this->valorTotal <= 0) {
            $erros[] = 'Valor total deve ser maior que zero';
        }

        // Verificar se já existe venda para esta semana/ano
        if ($this->semana && $this->ano) {
            $sql = "SELECT id FROM vendas WHERE semana = ? AND ano = ?";
            if ($this->id) {
                $sql .= " AND id != ?";
                $params = [$this->semana, $this->ano, $this->id];
            } else {
                $params = [$this->semana, $this->ano];
            }
            
            $existente = $this->db->fetchOne($sql, $params);
            if ($existente) {
                $erros[] = 'Já existe uma venda cadastrada para a semana ' . $this->semana . '/' . $this->ano;
            }
        }

        return $erros;
    }

    /**
     * Salvar venda no banco de dados
     */
    public function salvar() {
        try {
            $this->calcularDatas();
            
            if ($this->id) {
                // Atualizar venda existente
                $sql = "UPDATE vendas SET 
                        semana = ?, ano = ?, data_inicio = ?, data_fim = ?, 
                        valor_total = ?, observacoes = ?
                        WHERE id = ?";
                $params = [
                    $this->semana,
                    $this->ano,
                    $this->dataInicio->format('Y-m-d'),
                    $this->dataFim->format('Y-m-d'),
                    $this->valorTotal,
                    $this->observacoes,
                    $this->id
                ];
            } else {
                // Inserir nova venda
                $sql = "INSERT INTO vendas (semana, ano, data_inicio, data_fim, valor_total, observacoes, data_cadastro) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)";
                $params = [
                    $this->semana,
                    $this->ano,
                    $this->dataInicio->format('Y-m-d'),
                    $this->dataFim->format('Y-m-d'),
                    $this->valorTotal,
                    $this->observacoes,
                    $this->dataCadastro->format('Y-m-d H:i:s')
                ];
            }

            $result = $this->db->query($sql, $params);
            
            if (!$this->id) {
                $this->id = $this->db->lastInsertId();
            }

            return true;
        } catch (Exception $e) {
            error_log("Erro ao salvar venda: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Buscar venda por ID
     */
    public static function buscarPorId($id) {
        try {
            $db = Database::getInstance();
            $sql = "SELECT * FROM vendas WHERE id = ?";
            $dados = $db->fetchOne($sql, [$id]);

            if ($dados) {
                $venda = new self();
                $venda->id = $dados['id'];
                $venda->semana = $dados['semana'];
                $venda->ano = $dados['ano'];
                $venda->dataInicio = new DateTime($dados['data_inicio']);
                $venda->dataFim = new DateTime($dados['data_fim']);
                $venda->valorTotal = $dados['valor_total'];
                $venda->observacoes = $dados['observacoes'];
                $venda->dataCadastro = new DateTime($dados['data_cadastro']);
                return $venda;
            }

            return null;
        } catch (Exception $e) {
            error_log("Erro ao buscar venda: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Listar todas as vendas
     */
    public static function listarTodas($limite = null, $offset = 0) {
        try {
            $db = Database::getInstance();
            $sql = "SELECT * FROM vendas ORDER BY ano DESC, semana DESC";
            
            if ($limite) {
                $sql .= " LIMIT $limite OFFSET $offset";
            }

            return $db->fetchAll($sql);
        } catch (Exception $e) {
            error_log("Erro ao listar vendas: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Contar total de vendas
     */
    public static function contarTodas() {
        try {
            $db = Database::getInstance();
            $sql = "SELECT COUNT(*) as total FROM vendas";
            $resultado = $db->fetchOne($sql);
            return $resultado['total'] ?? 0;
        } catch (Exception $e) {
            error_log("Erro ao contar vendas: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Buscar vendas por período
     */
    public static function buscarPorPeriodo($dataInicio, $dataFim) {
        try {
            $db = Database::getInstance();
            $sql = "SELECT * FROM vendas 
                    WHERE data_inicio >= ? AND data_fim <= ?
                    ORDER BY ano DESC, semana DESC";
            return $db->fetchAll($sql, [$dataInicio, $dataFim]);
        } catch (Exception $e) {
            error_log("Erro ao buscar vendas por período: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Calcular total de vendas
     */
    public static function calcularTotalVendas($dataInicio = null, $dataFim = null) {
        try {
            $db = Database::getInstance();
            
            if ($dataInicio && $dataFim) {
                $sql = "SELECT SUM(valor_total) as total FROM vendas 
                        WHERE data_inicio >= ? AND data_fim <= ?";
                $params = [$dataInicio, $dataFim];
            } else {
                $sql = "SELECT SUM(valor_total) as total FROM vendas";
                $params = [];
            }

            $resultado = $db->fetchOne($sql, $params);
            return $resultado['total'] ?? 0;
        } catch (Exception $e) {
            error_log("Erro ao calcular total de vendas: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Obter semana atual
     */
    public static function getSemanaAtual() {
        return (int)date('W');
    }

    /**
     * Obter ano atual
     */
    public static function getAnoAtual() {
        return (int)date('Y');
    }

    /**
     * Formatar período da semana
     */
    public function formatarPeriodo() {
        if ($this->dataInicio && $this->dataFim) {
            return $this->dataInicio->format('d/m') . ' a ' . $this->dataFim->format('d/m/Y');
        }
        return '';
    }

    /**
     * Excluir venda
     */
    public function excluir() {
        try {
            if (!$this->id) {
                return false;
            }

            $sql = "DELETE FROM vendas WHERE id = ?";
            return $this->db->query($sql, [$this->id]);
        } catch (Exception $e) {
            error_log("Erro ao excluir venda: " . $e->getMessage());
            return false;
        }
    }
}
?>

