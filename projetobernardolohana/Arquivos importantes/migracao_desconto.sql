-- ============================================================
-- Migração: Desconto de 10% para pedidos com 7 itens ou mais
-- ============================================================
-- Execute UMA VEZ no banco `projetorrgi51`, depois de migracao_pedido_cliente.sql.
-- valor_total continua sendo o valor cheio do item; desconto guarda quanto
-- foi abatido dele. Total do item = valor_total - desconto.
-- Pedidos antigos e pedidos do garçom ficam com desconto 0.
-- ============================================================

ALTER TABLE pedido ADD COLUMN IF NOT EXISTS desconto DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER valor_total;
