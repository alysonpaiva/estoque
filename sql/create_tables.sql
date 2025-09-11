-- Sistema de Controle de Estoque - Pizzaria
-- Script de criação das tabelas

-- Criar banco de dados se não existir
CREATE DATABASE IF NOT EXISTS pizzaria_estoque CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE pizzaria_estoque;

-- Tabela de produtos
CREATE TABLE IF NOT EXISTS produtos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    unidade_medida VARCHAR(20) NOT NULL,
    ativo BOOLEAN DEFAULT TRUE,
    data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_nome (nome),
    INDEX idx_ativo (ativo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de lotes
CREATE TABLE IF NOT EXISTS lotes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    produto_id INT NOT NULL,
    preco_compra DECIMAL(10,2) NOT NULL,
    quantidade_comprada DECIMAL(10,3) NOT NULL,
    custo_por_unidade DECIMAL(10,4) NOT NULL,
    data_compra DATETIME DEFAULT CURRENT_TIMESTAMP,
    observacoes TEXT,
    FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE RESTRICT,
    INDEX idx_produto_id (produto_id),
    INDEX idx_data_compra (data_compra)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de produção
CREATE TABLE IF NOT EXISTS producao (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lote_id INT NOT NULL,
    quantidade_produzida INT NOT NULL,
    quantidade_materia_prima_usada DECIMAL(10,3) NOT NULL,
    custo_total_producao DECIMAL(10,2) NOT NULL,
    custo_por_porcao DECIMAL(10,4) NOT NULL,
    data_producao DATETIME DEFAULT CURRENT_TIMESTAMP,
    observacoes TEXT,
    FOREIGN KEY (lote_id) REFERENCES lotes(id) ON DELETE RESTRICT,
    INDEX idx_lote_id (lote_id),
    INDEX idx_data_producao (data_producao)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de retiradas
CREATE TABLE IF NOT EXISTS retiradas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    producao_id INT NOT NULL,
    quantidade_retirada INT NOT NULL,
    destino VARCHAR(100) NOT NULL,
    responsavel VARCHAR(100),
    data_retirada DATETIME DEFAULT CURRENT_TIMESTAMP,
    observacoes TEXT,
    FOREIGN KEY (producao_id) REFERENCES producao(id) ON DELETE RESTRICT,
    INDEX idx_producao_id (producao_id),
    INDEX idx_data_retirada (data_retirada),
    INDEX idx_destino (destino)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Views para relatórios

-- View de estoque atual por produto
CREATE OR REPLACE VIEW view_estoque_atual AS
SELECT 
    p.id as produto_id,
    p.nome as produto_nome,
    p.unidade_medida,
    COUNT(DISTINCT l.id) as total_lotes,
    COALESCE(SUM(l.quantidade_comprada), 0) as total_comprado,
    COALESCE(SUM(pr.quantidade_materia_prima_usada), 0) as total_usado_producao,
    COALESCE(SUM(l.quantidade_comprada) - SUM(pr.quantidade_materia_prima_usada), 0) as estoque_materia_prima,
    COALESCE(SUM(prod.quantidade_produzida), 0) as total_produzido,
    COALESCE(SUM(r.quantidade_retirada), 0) as total_retirado,
    COALESCE(SUM(prod.quantidade_produzida) - SUM(r.quantidade_retirada), 0) as estoque_porcoes,
    COALESCE(AVG(prod.custo_por_porcao), 0) as custo_medio_porcao
FROM produtos p
LEFT JOIN lotes l ON p.id = l.produto_id
LEFT JOIN producao pr ON l.id = pr.lote_id
LEFT JOIN producao prod ON l.id = prod.lote_id
LEFT JOIN retiradas r ON prod.id = r.producao_id
WHERE p.ativo = 1
GROUP BY p.id, p.nome, p.unidade_medida;

-- View de lotes com informações detalhadas
CREATE OR REPLACE VIEW view_lotes_detalhados AS
SELECT 
    l.*,
    p.nome as produto_nome,
    p.unidade_medida,
    COALESCE(SUM(pr.quantidade_materia_prima_usada), 0) as total_usado,
    (l.quantidade_comprada - COALESCE(SUM(pr.quantidade_materia_prima_usada), 0)) as quantidade_restante,
    COUNT(pr.id) as total_producoes
FROM lotes l
LEFT JOIN produtos p ON l.produto_id = p.id
LEFT JOIN producao pr ON l.id = pr.lote_id
GROUP BY l.id;

-- View de produções com informações detalhadas
CREATE OR REPLACE VIEW view_producoes_detalhadas AS
SELECT 
    pr.*,
    p.nome as produto_nome,
    p.unidade_medida,
    l.preco_compra as preco_lote,
    l.data_compra as data_lote,
    COALESCE(SUM(r.quantidade_retirada), 0) as total_retirado,
    (pr.quantidade_produzida - COALESCE(SUM(r.quantidade_retirada), 0)) as quantidade_disponivel,
    COUNT(r.id) as total_retiradas
FROM producao pr
LEFT JOIN lotes l ON pr.lote_id = l.id
LEFT JOIN produtos p ON l.produto_id = p.id
LEFT JOIN retiradas r ON pr.id = r.producao_id
GROUP BY pr.id;

-- View de retiradas com informações detalhadas
CREATE OR REPLACE VIEW view_retiradas_detalhadas AS
SELECT 
    r.*,
    p.nome as produto_nome,
    p.unidade_medida,
    pr.custo_por_porcao,
    (r.quantidade_retirada * pr.custo_por_porcao) as valor_total,
    pr.data_producao,
    l.data_compra as data_lote
FROM retiradas r
LEFT JOIN producao pr ON r.producao_id = pr.id
LEFT JOIN lotes l ON pr.lote_id = l.id
LEFT JOIN produtos p ON l.produto_id = p.id;

-- Inserir dados de exemplo (opcional)
INSERT IGNORE INTO produtos (nome, unidade_medida) VALUES
('Farinha de Trigo', 'Kg'),
('Açúcar', 'Kg'),
('Ovos', 'Dúzia'),
('Leite', 'Litro'),
('Manteiga', 'Kg');

-- Triggers para manter integridade

-- Trigger para calcular custo por unidade automaticamente
DELIMITER //
CREATE TRIGGER tr_lotes_custo_por_unidade 
BEFORE INSERT ON lotes
FOR EACH ROW
BEGIN
    IF NEW.quantidade_comprada > 0 THEN
        SET NEW.custo_por_unidade = NEW.preco_compra / NEW.quantidade_comprada;
    ELSE
        SET NEW.custo_por_unidade = 0;
    END IF;
END//

CREATE TRIGGER tr_lotes_custo_por_unidade_update
BEFORE UPDATE ON lotes
FOR EACH ROW
BEGIN
    IF NEW.quantidade_comprada > 0 THEN
        SET NEW.custo_por_unidade = NEW.preco_compra / NEW.quantidade_comprada;
    ELSE
        SET NEW.custo_por_unidade = 0;
    END IF;
END//
DELIMITER ;

-- Índices adicionais para performance
CREATE INDEX idx_produtos_nome_ativo ON produtos(nome, ativo);
CREATE INDEX idx_lotes_produto_data ON lotes(produto_id, data_compra);
CREATE INDEX idx_producao_lote_data ON producao(lote_id, data_producao);
CREATE INDEX idx_retiradas_producao_data ON retiradas(producao_id, data_retirada);

-- Comentários nas tabelas
ALTER TABLE produtos COMMENT = 'Tabela de produtos/ingredientes';
ALTER TABLE lotes COMMENT = 'Tabela de lotes de compra de produtos';
ALTER TABLE producao COMMENT = 'Tabela de produções realizadas';
ALTER TABLE retiradas COMMENT = 'Tabela de retiradas para pizzaria';

