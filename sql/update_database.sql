-- Script de Atualização do Banco de Dados
-- Novas funcionalidades: Produtos Prontos, Itens Extras, CMV

-- 1. Adicionar campo tipo_produto na tabela produtos
ALTER TABLE produtos 
ADD COLUMN tipo_produto ENUM('materia_prima', 'produto_pronto') DEFAULT 'materia_prima' AFTER unidade_medida;

-- 2. Criar tabela de entradas diretas (para produtos prontos)
CREATE TABLE IF NOT EXISTS entradas_diretas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    produto_id INT NOT NULL,
    quantidade_entrada DECIMAL(10,3) NOT NULL,
    preco_unitario DECIMAL(10,4) NOT NULL,
    valor_total DECIMAL(10,2) NOT NULL,
    fornecedor VARCHAR(100),
    nota_fiscal VARCHAR(50),
    data_entrada DATETIME DEFAULT CURRENT_TIMESTAMP,
    observacoes TEXT,
    FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE RESTRICT,
    INDEX idx_produto_id (produto_id),
    INDEX idx_data_entrada (data_entrada)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Criar tabela de retiradas diretas (para produtos prontos)
CREATE TABLE IF NOT EXISTS retiradas_diretas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    produto_id INT NOT NULL,
    quantidade_retirada DECIMAL(10,3) NOT NULL,
    destino VARCHAR(100) NOT NULL,
    responsavel VARCHAR(100),
    data_retirada DATETIME DEFAULT CURRENT_TIMESTAMP,
    observacoes TEXT,
    FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE RESTRICT,
    INDEX idx_produto_id (produto_id),
    INDEX idx_data_retirada (data_retirada),
    INDEX idx_destino (destino)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Criar tabela de itens extras na produção
CREATE TABLE IF NOT EXISTS producao_itens_extras (
    id INT AUTO_INCREMENT PRIMARY KEY,
    producao_id INT NOT NULL,
    descricao VARCHAR(100) NOT NULL,
    quantidade DECIMAL(10,3) NOT NULL,
    unidade VARCHAR(20) NOT NULL,
    valor_unitario DECIMAL(10,4) NOT NULL,
    valor_total DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (producao_id) REFERENCES producao(id) ON DELETE CASCADE,
    INDEX idx_producao_id (producao_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Adicionar campo para custos extras na tabela produção
ALTER TABLE producao 
ADD COLUMN custo_itens_extras DECIMAL(10,2) DEFAULT 0 AFTER custo_total_producao;

-- 6. Criar view para estoque de produtos prontos
CREATE OR REPLACE VIEW view_estoque_produtos_prontos AS
SELECT 
    p.id as produto_id,
    p.nome as produto_nome,
    p.unidade_medida,
    COALESCE(SUM(ed.quantidade_entrada), 0) as total_entradas,
    COALESCE(SUM(rd.quantidade_retirada), 0) as total_retiradas,
    COALESCE(SUM(ed.quantidade_entrada) - SUM(rd.quantidade_retirada), 0) as estoque_atual,
    COALESCE(AVG(ed.preco_unitario), 0) as preco_medio,
    COALESCE(SUM(ed.valor_total), 0) as valor_total_investido
FROM produtos p
LEFT JOIN entradas_diretas ed ON p.id = ed.produto_id
LEFT JOIN retiradas_diretas rd ON p.id = rd.produto_id
WHERE p.tipo_produto = 'produto_pronto' AND p.ativo = 1
GROUP BY p.id, p.nome, p.unidade_medida;

-- 7. Criar view para CMV (Custo da Mercadoria Vendida)
CREATE OR REPLACE VIEW view_cmv AS
SELECT 
    'materia_prima' as tipo,
    SUM(l.preco_compra) as compras_periodo,
    SUM(r.quantidade_retirada * pr.custo_por_porcao) as vendas_periodo,
    SUM(ea.estoque_porcoes * ea.custo_medio_porcao) as estoque_final
FROM lotes l
LEFT JOIN producao pr ON l.id = pr.lote_id
LEFT JOIN retiradas r ON pr.id = r.producao_id
LEFT JOIN view_estoque_atual ea ON l.produto_id = ea.produto_id

UNION ALL

SELECT 
    'produto_pronto' as tipo,
    SUM(ed.valor_total) as compras_periodo,
    SUM(rd.quantidade_retirada * ed.preco_unitario) as vendas_periodo,
    SUM(ep.estoque_atual * ep.preco_medio) as estoque_final
FROM entradas_diretas ed
LEFT JOIN retiradas_diretas rd ON ed.produto_id = rd.produto_id
LEFT JOIN view_estoque_produtos_prontos ep ON ed.produto_id = ep.produto_id;

-- 8. Atualizar view de produções para incluir itens extras
CREATE OR REPLACE VIEW view_producoes_completas AS
SELECT 
    pr.*,
    p.nome as produto_nome,
    p.unidade_medida,
    l.preco_compra as preco_lote,
    l.data_compra as data_lote,
    COALESCE(SUM(r.quantidade_retirada), 0) as total_retirado,
    (pr.quantidade_produzida - COALESCE(SUM(r.quantidade_retirada), 0)) as quantidade_disponivel,
    COUNT(r.id) as total_retiradas,
    COALESCE(SUM(pie.valor_total), 0) as total_itens_extras,
    (pr.custo_total_producao + COALESCE(SUM(pie.valor_total), 0)) as custo_total_final
FROM producao pr
LEFT JOIN lotes l ON pr.lote_id = l.id
LEFT JOIN produtos p ON l.produto_id = p.id
LEFT JOIN retiradas r ON pr.id = r.producao_id
LEFT JOIN producao_itens_extras pie ON pr.id = pie.producao_id
GROUP BY pr.id;

-- 9. Trigger para calcular valor total nas entradas diretas
DELIMITER //
CREATE TRIGGER tr_entradas_diretas_valor_total 
BEFORE INSERT ON entradas_diretas
FOR EACH ROW
BEGIN
    SET NEW.valor_total = NEW.quantidade_entrada * NEW.preco_unitario;
END//

CREATE TRIGGER tr_entradas_diretas_valor_total_update
BEFORE UPDATE ON entradas_diretas
FOR EACH ROW
BEGIN
    SET NEW.valor_total = NEW.quantidade_entrada * NEW.preco_unitario;
END//

-- 10. Trigger para calcular valor total nos itens extras
CREATE TRIGGER tr_itens_extras_valor_total 
BEFORE INSERT ON producao_itens_extras
FOR EACH ROW
BEGIN
    SET NEW.valor_total = NEW.quantidade * NEW.valor_unitario;
END//

CREATE TRIGGER tr_itens_extras_valor_total_update
BEFORE UPDATE ON producao_itens_extras
FOR EACH ROW
BEGIN
    SET NEW.valor_total = NEW.quantidade * NEW.valor_unitario;
END//
DELIMITER ;

-- 11. Inserir alguns produtos prontos de exemplo
INSERT IGNORE INTO produtos (nome, unidade_medida, tipo_produto) VALUES
('Chocolate Bisnaga', 'Unidade', 'produto_pronto'),
('Farinha Pronta', 'Kg', 'produto_pronto'),
('Molho de Tomate', 'Lata', 'produto_pronto'),
('Queijo Mussarela', 'Kg', 'produto_pronto'),
('Refrigerante 2L', 'Unidade', 'produto_pronto');

-- Comentários nas novas tabelas
ALTER TABLE entradas_diretas COMMENT = 'Entradas diretas de produtos prontos';
ALTER TABLE retiradas_diretas COMMENT = 'Retiradas diretas de produtos prontos';
ALTER TABLE producao_itens_extras COMMENT = 'Itens extras utilizados na produção';


-- 12. Tabela de vendas semanais
CREATE TABLE IF NOT EXISTS vendas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    semana INT NOT NULL,
    ano INT NOT NULL,
    data_inicio DATE NOT NULL,
    data_fim DATE NOT NULL,
    valor_total DECIMAL(10,2) NOT NULL,
    observacoes TEXT,
    data_cadastro DATETIME NOT NULL,
    UNIQUE KEY unique_semana_ano (semana, ano),
    INDEX idx_periodo (data_inicio, data_fim),
    INDEX idx_ano_semana (ano, semana)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 13. View para relatório de vendas vs custos
CREATE OR REPLACE VIEW view_vendas_custos AS
SELECT 
    v.semana,
    v.ano,
    v.data_inicio,
    v.data_fim,
    v.valor_total as vendas,
    COALESCE(SUM(r.quantidade_retirada * pr.custo_por_porcao), 0) as custo_retiradas_producao,
    COALESCE(SUM(rd.quantidade_retirada * ed.preco_unitario), 0) as custo_retiradas_diretas,
    (COALESCE(SUM(r.quantidade_retirada * pr.custo_por_porcao), 0) + 
     COALESCE(SUM(rd.quantidade_retirada * ed.preco_unitario), 0)) as custo_total,
    v.valor_total - (COALESCE(SUM(r.quantidade_retirada * pr.custo_por_porcao), 0) + 
                     COALESCE(SUM(rd.quantidade_retirada * ed.preco_unitario), 0)) as lucro_bruto,
    CASE 
        WHEN v.valor_total > 0 THEN 
            ((COALESCE(SUM(r.quantidade_retirada * pr.custo_por_porcao), 0) + 
              COALESCE(SUM(rd.quantidade_retirada * ed.preco_unitario), 0)) / v.valor_total) * 100
        ELSE 0 
    END as cmv_percentual
FROM vendas v
LEFT JOIN retiradas r ON DATE(r.data_retirada) BETWEEN v.data_inicio AND v.data_fim
LEFT JOIN producao pr ON r.producao_id = pr.id
LEFT JOIN retiradas_diretas rd ON DATE(rd.data_retirada) BETWEEN v.data_inicio AND v.data_fim
LEFT JOIN entradas_diretas ed ON rd.produto_id = ed.produto_id
GROUP BY v.id, v.semana, v.ano, v.data_inicio, v.data_fim, v.valor_total
ORDER BY v.ano DESC, v.semana DESC;

-- Comentário na tabela vendas
ALTER TABLE vendas COMMENT = 'Vendas semanais para cálculo de CMV e relatórios financeiros';

