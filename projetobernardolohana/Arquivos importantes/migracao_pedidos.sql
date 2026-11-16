-- ============================================================
-- Migração: Pedidos (histórico) — Figma "Página Pedidos" (2:97)
-- ============================================================
-- Execute UMA VEZ no banco `projetorrgi51`.
-- Pré-requisito: rodar antes o script migracao_produto_estoque.sql
-- (a tela de pedidos lista os produtos ativos).
-- ============================================================

CREATE TABLE IF NOT EXISTS pedido (
    id INT AUTO_INCREMENT PRIMARY KEY,
    produto_id INT NULL,                       -- some (NULL) se o produto for excluído depois...
    produto_nome VARCHAR(255) NOT NULL,        -- ...por isso o nome fica gravado: o histórico nunca perde o nome
    quantidade INT NOT NULL DEFAULT 1,
    data_pedido DATETIME NOT NULL,             -- campo "Hora/Data" do formulário
    hora_recebimento TIME NULL,                -- "Hora do recebimento" (opcional)
    nota_fiscal VARCHAR(60) NULL,              -- "Nota Fiscal" (opcional)
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_pedido_data (data_pedido)
);

-- OPCIONAL: liga o pedido ao produto (ao excluir o produto, produto_id vira NULL e o histórico continua).
-- Se der erro de "foreign key constraint", o tipo de produto.id no seu banco é diferente de INT
-- (ex.: INT UNSIGNED). O sistema funciona normalmente sem esta linha.
ALTER TABLE pedido
    ADD CONSTRAINT fk_pedido_produto
    FOREIGN KEY (produto_id) REFERENCES produto(id)
    ON DELETE SET NULL;
