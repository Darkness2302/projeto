-- ============================================================
-- Migração: Opções de variação (ex.: P / M / G) com preço próprio
-- ============================================================
-- Execute UMA VEZ no banco `projetorrgi51`, depois de migracao_pedidos.sql.
-- O campo produto.variacao continua sendo o nome do grupo (ex.: "Tamanho").
-- As opções ficam nesta tabela e são cadastradas na tela de Estoque
-- (Adição/Edição de produto). O pedido grava a opção escolhida e o preço dela.
-- ============================================================

CREATE TABLE IF NOT EXISTS produto_variacao (
    id INT AUTO_INCREMENT PRIMARY KEY,
    produto_id INT NOT NULL,
    nome VARCHAR(60) NOT NULL,
    preco DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    KEY idx_variacao_produto (produto_id),
    CONSTRAINT fk_variacao_produto FOREIGN KEY (produto_id) REFERENCES produto(id) ON DELETE CASCADE
);

-- Texto da variação escolhida no pedido (ex.: "Tamanho: M"), gravado como histórico
ALTER TABLE pedido ADD COLUMN IF NOT EXISTS variacao VARCHAR(120) NULL AFTER produto_nome;
