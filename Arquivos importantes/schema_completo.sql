
-- ============================================================

CREATE DATABASE IF NOT EXISTS projetorrgi51 CHARACTER SET utf8mb4;
USE projetorrgi51;

-- ------------------------------------------------------------
-- usuario — login e perfil de acesso (cliente / garcom / gerente)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS usuario (
    id     INT AUTO_INCREMENT PRIMARY KEY,
    nome   VARCHAR(150) NOT NULL,
    email  VARCHAR(150) NOT NULL UNIQUE,
    senha  VARCHAR(255) NOT NULL,               -- hash de password_hash(), não a senha em texto
    perfil VARCHAR(30)  NOT NULL DEFAULT 'cliente',  -- valores usados hoje: cliente | garcom | gerente
    ativo  TINYINT(1)   NOT NULL DEFAULT 1
);

-- ------------------------------------------------------------
-- categoria1 — categorias de produto (Refeições, Bebidas, ...)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS categoria1 (
    id    INT AUTO_INCREMENT PRIMARY KEY,
    nome  VARCHAR(100) NOT NULL,
    ativo TINYINT(1)   NOT NULL DEFAULT 1
);

-- ------------------------------------------------------------
-- fornecedor — fornecedores de mercadoria do restaurante
-- (não confundir com o perfil de usuário "garcom")
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS fornecedor (
    id                INT AUTO_INCREMENT PRIMARY KEY,
    nome              VARCHAR(150) NOT NULL,
    cpf               VARCHAR(20)  NULL,
    lugar             VARCHAR(150) NULL,
    produtos          VARCHAR(255) NULL,
    logo              VARCHAR(255) NULL,        -- não usado hoje (a foto é resolvida por convenção de nome de arquivo)
    data_recebimento  DATE NULL,
    hora_recebimento  TIME NULL,
    ativo             TINYINT(1) NOT NULL DEFAULT 1
);

-- ------------------------------------------------------------
-- produto — cardápio / estoque
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS produto (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    categoria_id  INT NOT NULL,
    nome          VARCHAR(255) NOT NULL,
    descricao     TEXT NULL,
    variacao      VARCHAR(150) NULL,
    preco         DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    estoque_qtd   INT NOT NULL DEFAULT 0,
    fornecedor_id INT NULL,
    ativo         TINYINT(1) NOT NULL DEFAULT 1,
    CONSTRAINT fk_produto_categoria
        FOREIGN KEY (categoria_id) REFERENCES categoria1(id),
    CONSTRAINT fk_produto_fornecedor
        FOREIGN KEY (fornecedor_id) REFERENCES fornecedor(id) ON DELETE SET NULL
);

-- ------------------------------------------------------------
-- pedido — histórico de pedidos (tela "Pedidos")
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS pedido (
    id                INT AUTO_INCREMENT PRIMARY KEY,
    produto_id        INT NULL,                 -- fica NULL se o produto for excluído depois
    produto_nome      VARCHAR(255) NOT NULL,     -- nome gravado na hora: o histórico não perde o nome
    quantidade        INT NOT NULL DEFAULT 1,
    data_pedido       DATETIME NOT NULL,
    hora_recebimento  TIME NULL,
    nota_fiscal       VARCHAR(60) NULL,
    criado_em         TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_pedido_data (data_pedido),
    CONSTRAINT fk_pedido_produto
        FOREIGN KEY (produto_id) REFERENCES produto(id) ON DELETE SET NULL
);
