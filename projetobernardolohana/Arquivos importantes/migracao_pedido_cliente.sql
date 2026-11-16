-- ============================================================
-- Migração: Pedidos feitos pelo cliente
-- ============================================================
-- Execute UMA VEZ no banco `projetorrgi51`, depois de migracao_variacoes.sql.
-- Liga cada pedido à conta do cliente que fez (usuario_id).
-- Pedidos do garçom continuam sem cliente (usuario_id NULL).
-- ============================================================

ALTER TABLE pedido ADD COLUMN IF NOT EXISTS usuario_id INT NULL AFTER variacao;

ALTER TABLE pedido
    ADD CONSTRAINT fk_pedido_usuario
    FOREIGN KEY (usuario_id) REFERENCES usuario(id)
    ON DELETE SET NULL;
