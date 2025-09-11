<?php
/**
 * Classe Relatorio
 * Sistema de Controle de Estoque - Pizzaria
 */

class Relatorio {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Dashboard principal com dados resumidos
     */
    public function dashboard() {
        try {
            $dados = [];
            
            // Contadores básicos
            $dados['total_produtos'] = $this->contarProdutos();
            $dados['total_lotes'] = $this->contarLotes();
            $dados['total_producoes'] = $this->contarProducoes();
            $dados['total_retiradas'] = $this->contarRetiradas();
            
            // Valores financeiros
            $dados['valor_investido'] = $this->calcularValorInvestido();
            $dados['custo_producao'] = $this->calcularCustoProducao();
            $dados['valor_retiradas'] = $this->calcularValorRetiradas();
            $dados['estoque_porcoes'] = $this->calcularEstoquePorcoes();
            
            // Alertas
            $dados['alertas_estoque'] = $this->alertasEstoque();
            
            // Últimas movimentações
            $dados['ultimos_lotes'] = $this->ultimosLotes(5);
            $dados['ultimas_producoes'] = $this->ultimasProducoes(5);
            $dados['ultimas_retiradas'] = $this->ultimasRetiradas(5);
            
            return $dados;
        } catch (Exception $e) {
            debugLog("Erro no dashboard: " . $e->getMessage());
            return $this->dashboardVazio();
        }
    }
    
    /**
     * Retorna dashboard vazio em caso de erro
     */
    private function dashboardVazio() {
        return [
            'total_produtos' => 0,
            'total_lotes' => 0,
            'total_producoes' => 0,
            'total_retiradas' => 0,
            'valor_investido' => 0,
            'custo_producao' => 0,
            'valor_retiradas' => 0,
            'estoque_porcoes' => 0,
            'alertas_estoque' => [],
            'ultimos_lotes' => [],
            'ultimas_producoes' => [],
            'ultimas_retiradas' => []
        ];
    }
    
    /**
     * Conta produtos ativos
     */
    private function contarProdutos() {
        try {
            $sql = "SELECT COUNT(*) as total FROM produtos WHERE ativo = 1";
            $result = $this->db->fetchOne($sql);
            return (int)$result['total'];
        } catch (Exception $e) {
            debugLog("Erro ao contar produtos: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Conta lotes
     */
    private function contarLotes() {
        try {
            $sql = "SELECT COUNT(*) as total FROM lotes";
            $result = $this->db->fetchOne($sql);
            return (int)$result['total'];
        } catch (Exception $e) {
            debugLog("Erro ao contar lotes: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Conta produções
     */
    private function contarProducoes() {
        try {
            $sql = "SELECT COUNT(*) as total FROM producao";
            $result = $this->db->fetchOne($sql);
            return (int)$result['total'];
        } catch (Exception $e) {
            debugLog("Erro ao contar produções: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Conta retiradas
     */
    private function contarRetiradas() {
        try {
            $sql = "SELECT COUNT(*) as total FROM retiradas";
            $result = $this->db->fetchOne($sql);
            return (int)$result['total'];
        } catch (Exception $e) {
            debugLog("Erro ao contar retiradas: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Calcula valor total investido em lotes
     */
    private function calcularValorInvestido() {
        try {
            $sql = "SELECT COALESCE(SUM(preco_compra), 0) as total FROM lotes";
            $result = $this->db->fetchOne($sql);
            return (float)$result['total'];
        } catch (Exception $e) {
            debugLog("Erro ao calcular valor investido: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Calcula custo total de produção
     */
    private function calcularCustoProducao() {
        try {
            $sql = "SELECT COALESCE(SUM(custo_total_producao), 0) as total FROM producao";
            $result = $this->db->fetchOne($sql);
            return (float)$result['total'];
        } catch (Exception $e) {
            debugLog("Erro ao calcular custo produção: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Calcula valor total das retiradas
     */
    private function calcularValorRetiradas() {
        try {
            $sql = "SELECT COALESCE(SUM(r.quantidade_retirada * pr.custo_por_porcao), 0) as total
                    FROM retiradas r
                    LEFT JOIN producao pr ON r.producao_id = pr.id";
            $result = $this->db->fetchOne($sql);
            return (float)$result['total'];
        } catch (Exception $e) {
            debugLog("Erro ao calcular valor retiradas: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Calcula total de porções em estoque
     */
    private function calcularEstoquePorcoes() {
        try {
            $sql = "SELECT COALESCE(SUM(pr.quantidade_produzida - COALESCE(r.total_retirado, 0)), 0) as total
                    FROM producao pr
                    LEFT JOIN (
                        SELECT producao_id, SUM(quantidade_retirada) as total_retirado
                        FROM retiradas
                        GROUP BY producao_id
                    ) r ON pr.id = r.producao_id";
            $result = $this->db->fetchOne($sql);
            return (int)$result['total'];
        } catch (Exception $e) {
            debugLog("Erro ao calcular estoque porções: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Alertas de estoque baixo
     */
    private function alertasEstoque() {
        try {
            $sql = "SELECT p.nome as produto_nome, 
                           COALESCE(SUM(pr.quantidade_produzida - COALESCE(r.total_retirado, 0)), 0) as estoque_atual
                    FROM produtos p
                    LEFT JOIN lotes l ON p.id = l.produto_id
                    LEFT JOIN producao pr ON l.id = pr.lote_id
                    LEFT JOIN (
                        SELECT producao_id, SUM(quantidade_retirada) as total_retirado
                        FROM retiradas
                        GROUP BY producao_id
                    ) r ON pr.id = r.producao_id
                    WHERE p.ativo = 1
                    GROUP BY p.id, p.nome
                    HAVING estoque_atual < :estoque_minimo
                    ORDER BY estoque_atual ASC";
            
            $results = $this->db->query($sql, [':estoque_minimo' => ESTOQUE_MINIMO_ALERTA]);
            return $results ?: [];
        } catch (Exception $e) {
            debugLog("Erro ao buscar alertas de estoque: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Últimos lotes cadastrados
     */
    private function ultimosLotes($limite = 5) {
        try {
            $sql = "SELECT l.*, p.nome as produto_nome
                    FROM lotes l
                    LEFT JOIN produtos p ON l.produto_id = p.id
                    ORDER BY l.data_compra DESC
                    LIMIT :limite";
            
            $results = $this->db->query($sql, [':limite' => $limite]);
            return $results ?: [];
        } catch (Exception $e) {
            debugLog("Erro ao buscar últimos lotes: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Últimas produções
     */
    private function ultimasProducoes($limite = 5) {
        try {
            $sql = "SELECT pr.*, p.nome as produto_nome
                    FROM producao pr
                    LEFT JOIN lotes l ON pr.lote_id = l.id
                    LEFT JOIN produtos p ON l.produto_id = p.id
                    ORDER BY pr.data_producao DESC
                    LIMIT :limite";
            
            $results = $this->db->query($sql, [':limite' => $limite]);
            return $results ?: [];
        } catch (Exception $e) {
            debugLog("Erro ao buscar últimas produções: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Últimas retiradas
     */
    private function ultimasRetiradas($limite = 5) {
        try {
            $sql = "SELECT r.*, p.nome as produto_nome
                    FROM retiradas r
                    LEFT JOIN producao pr ON r.producao_id = pr.id
                    LEFT JOIN lotes l ON pr.lote_id = l.id
                    LEFT JOIN produtos p ON l.produto_id = p.id
                    ORDER BY r.data_retirada DESC
                    LIMIT :limite";
            
            $results = $this->db->query($sql, [':limite' => $limite]);
            return $results ?: [];
        } catch (Exception $e) {
            debugLog("Erro ao buscar últimas retiradas: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Relatório de entradas (lotes) por período
     */
    public function relatorioEntradas(DateTime $dataInicio, DateTime $dataFim) {
        try {
            $sql = "SELECT l.*, p.nome as produto_nome
                    FROM lotes l
                    LEFT JOIN produtos p ON l.produto_id = p.id
                    WHERE l.data_compra BETWEEN :data_inicio AND :data_fim
                    ORDER BY l.data_compra DESC";
            
            $params = [
                ':data_inicio' => $dataInicio->format('Y-m-d 00:00:00'),
                ':data_fim' => $dataFim->format('Y-m-d 23:59:59')
            ];
            
            $results = $this->db->query($sql, $params);
            return $results ?: [];
        } catch (Exception $e) {
            debugLog("Erro no relatório de entradas: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Relatório de produção por período
     */
    public function relatorioProducao(DateTime $dataInicio, DateTime $dataFim) {
        try {
            $sql = "SELECT pr.*, p.nome as produto_nome, l.preco_compra
                    FROM producao pr
                    LEFT JOIN lotes l ON pr.lote_id = l.id
                    LEFT JOIN produtos p ON l.produto_id = p.id
                    WHERE pr.data_producao BETWEEN :data_inicio AND :data_fim
                    ORDER BY pr.data_producao DESC";
            
            $params = [
                ':data_inicio' => $dataInicio->format('Y-m-d 00:00:00'),
                ':data_fim' => $dataFim->format('Y-m-d 23:59:59')
            ];
            
            $results = $this->db->query($sql, $params);
            return $results ?: [];
        } catch (Exception $e) {
            debugLog("Erro no relatório de produção: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Relatório de retiradas por período
     */
    public function relatorioRetiradas(DateTime $dataInicio, DateTime $dataFim) {
        try {
            $sql = "SELECT r.*, p.nome as produto_nome, pr.custo_por_porcao
                    FROM retiradas r
                    LEFT JOIN producao pr ON r.producao_id = pr.id
                    LEFT JOIN lotes l ON pr.lote_id = l.id
                    LEFT JOIN produtos p ON l.produto_id = p.id
                    WHERE r.data_retirada BETWEEN :data_inicio AND :data_fim
                    ORDER BY r.data_retirada DESC";
            
            $params = [
                ':data_inicio' => $dataInicio->format('Y-m-d 00:00:00'),
                ':data_fim' => $dataFim->format('Y-m-d 23:59:59')
            ];
            
            $results = $this->db->query($sql, $params);
            return $results ?: [];
        } catch (Exception $e) {
            debugLog("Erro no relatório de retiradas: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Relatório de estoque atual
     */
    public function relatorioEstoqueAtual() {
        try {
            $sql = "SELECT p.nome as produto_nome,
                           COUNT(DISTINCT l.id) as total_lotes,
                           COALESCE(SUM(l.quantidade_comprada), 0) as total_comprado,
                           COALESCE(SUM(pr.quantidade_materia_prima_usada), 0) as total_usado,
                           COALESCE(SUM(l.quantidade_comprada) - SUM(pr.quantidade_materia_prima_usada), 0) as estoque_materia_prima,
                           COALESCE(SUM(prod.quantidade_produzida), 0) as total_produzido,
                           COALESCE(SUM(r.quantidade_retirada), 0) as total_retirado,
                           COALESCE(SUM(prod.quantidade_produzida) - SUM(r.quantidade_retirada), 0) as estoque_porcoes
                    FROM produtos p
                    LEFT JOIN lotes l ON p.id = l.produto_id
                    LEFT JOIN producao pr ON l.id = pr.lote_id
                    LEFT JOIN producao prod ON l.id = prod.lote_id
                    LEFT JOIN retiradas r ON prod.id = r.producao_id
                    WHERE p.ativo = 1
                    GROUP BY p.id, p.nome
                    ORDER BY p.nome";
            
            $results = $this->db->query($sql);
            return $results ?: [];
        } catch (Exception $e) {
            debugLog("Erro no relatório de estoque atual: " . $e->getMessage());
            return [];
        }
    }
}
?>

