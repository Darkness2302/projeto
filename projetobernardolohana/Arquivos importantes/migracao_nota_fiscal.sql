-- ============================================================
-- Migração: Nota Fiscal automática
-- ============================================================
-- Execute UMA VEZ no banco `projetorrgi51`, depois de já ter
-- rodado migracao_pedidos.sql (precisa da tabela `pedido`).
--
-- O que muda: até agora, o campo "Nota Fiscal" do pedido era
-- digitado à mão no formulário. A partir desta migração, o sistema
-- GERA o número da nota sozinho ao registrar o pedido (não existe
-- mais input manual pra isso) e também passa a guardar o preço do
-- produto NO MOMENTO do pedido — importante porque se o preço do
-- produto mudar depois no Estoque, a nota fiscal de um pedido
-- antigo não pode mudar de valor junto.
-- ============================================================

ALTER TABLE pedido
    ADD COLUMN preco_unitario DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER quantidade,
    ADD COLUMN valor_total    DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER preco_unitario;

-- Pedidos que já existiam antes desta migração não tinham preço salvo.
-- Esta linha preenche o preço/total deles com o preço ATUAL do produto
-- (é uma aproximação — não há como recuperar o preço exato da época para
-- pedidos antigos). Pedidos cujo produto já foi excluído ficam com 0,00.
UPDATE pedido p
LEFT JOIN produto pr ON pr.id = p.produto_id
SET p.preco_unitario = COALESCE(pr.preco, 0.00),
    p.valor_total     = COALESCE(pr.preco, 0.00) * p.quantidade
WHERE p.preco_unitario = 0.00;

-- Pedidos antigos também não tinham nota fiscal gerada pelo sistema
-- (campo era texto livre, muitos ficaram em branco). Gera o número
-- padrão pra eles também, no mesmo formato novo.
UPDATE pedido
SET nota_fiscal = CONCAT('NF-', YEAR(data_pedido), '-', LPAD(id, 6, '0'))
WHERE nota_fiscal IS NULL OR nota_fiscal = '';
