-- ============================================================
-- MIGRAÇÃO: Histórico de mercadoria (tabela entrada_mercadoria)
-- Execute UMA VEZ no banco `projetorrgi51`.
-- Cada movimentação de estoque vira uma linha:
--   tipo 'entrada' -> fornecedor entregou o produto (soma no estoque)
--   tipo 'venda'   -> pedido de cliente ou garçom (baixa no estoque)
-- ============================================================
ALTER TABLE entrada_mercadoria
    ADD COLUMN IF NOT EXISTS tipo ENUM('entrada','venda') NOT NULL DEFAULT 'entrada' AFTER id,
    ADD COLUMN IF NOT EXISTS produto_id INT NULL AFTER tipo,
    ADD COLUMN IF NOT EXISTS produto_nome VARCHAR(100) NULL AFTER produto_id,
    ADD COLUMN IF NOT EXISTS fornecedor_id INT NULL AFTER produto_nome,
    ADD COLUMN IF NOT EXISTS pedido_id INT NULL AFTER fornecedor_id,
    ADD COLUMN IF NOT EXISTS usuario_id INT NULL AFTER pedido_id,
    ADD COLUMN IF NOT EXISTS quantidade INT NOT NULL DEFAULT 0 AFTER usuario_id,
    ADD COLUMN IF NOT EXISTS valor_unitario DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER quantidade,
    ADD COLUMN IF NOT EXISTS estoque_antes INT NULL AFTER valor_unitario,
    ADD COLUMN IF NOT EXISTS estoque_depois INT NULL AFTER estoque_antes,
    ADD COLUMN IF NOT EXISTS criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE entrada_mercadoria
    ADD CONSTRAINT fk_mov_produto FOREIGN KEY (produto_id) REFERENCES produto(id) ON DELETE SET NULL,
    ADD CONSTRAINT fk_mov_fornecedor FOREIGN KEY (fornecedor_id) REFERENCES fornecedor(id) ON DELETE SET NULL,
    ADD CONSTRAINT fk_mov_pedido FOREIGN KEY (pedido_id) REFERENCES pedido(id) ON DELETE SET NULL,
    ADD CONSTRAINT fk_mov_usuario FOREIGN KEY (usuario_id) REFERENCES usuario(id) ON DELETE SET NULL;
