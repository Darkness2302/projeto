-- Atualização p Banco de Dados V1->V1.5/Rodar no V1 já montado --
-- ============================================================
CREATE TABLE IF NOT EXISTS fornecedor (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    cpf VARCHAR(20) NULL,
    lugar VARCHAR(150) NULL,
    produtos VARCHAR(255) NULL,
    logo VARCHAR(255) NULL,
    data_recebimento DATE NULL,
    hora_recebimento TIME NULL,
    ativo TINYINT(1) NOT NULL DEFAULT 1
);

-- 2) Novos campos na tabela produto
ALTER TABLE produto ADD COLUMN variacao      VARCHAR(150)   NULL           AFTER descricao;
ALTER TABLE produto ADD COLUMN preco         DECIMAL(10,2)  NOT NULL DEFAULT 0.00 AFTER variacao;
ALTER TABLE produto ADD COLUMN estoque_qtd   INT            NOT NULL DEFAULT 0    AFTER preco;
ALTER TABLE produto ADD COLUMN fornecedor_id INT            NULL           AFTER estoque_qtd;

-- 3) Chave estrangeira produto -> fornecedor (opcional, mas recomendado)
ALTER TABLE produto
    ADD CONSTRAINT fk_produto_fornecedor
    FOREIGN KEY (fornecedor_id) REFERENCES fornecedor(id)
    ON DELETE SET NULL;

-- ============================================================
-- Dica: se preferir popular fornecedores de teste, use:
-- INSERT INTO fornecedor (nome, ativo) VALUES ('Distribuidora Sabor Caseiro', 1);
-- ============================================================
