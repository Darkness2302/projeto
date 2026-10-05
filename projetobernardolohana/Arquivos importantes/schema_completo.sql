-- ============================================================
-- Panela Quente — schema completo (todas as tabelas)
-- ============================================================
--
-- ⚠️ LEIA ANTES DE RODAR
--
-- Este arquivo serve para criar o banco `projetorrgi51` DO ZERO
-- (ex.: um ambiente novo, ou seu computador atual sem o banco).
--
-- `usuario`, `categoria1` e as colunas originais de `produto`
-- (categoria_id, nome, descricao, ativo) eu NUNCA criei — elas já
-- existiam no seu projeto antes de eu começar a ajudar. Reconstruí
-- essas três aqui só olhando como o código usa cada coluna (nome,
-- tipo aproximado), não copiando do seu banco real. Se você já tem
-- um banco `projetorrgi51` rodando com dados, NÃO rode este arquivo
-- nele — os tipos/tamanhos exatos (ex. VARCHAR(100) vs VARCHAR(150))
-- podem não bater com o que você já tem, e um CREATE TABLE aqui é
-- ignorado silenciosamente se a tabela já existir (não atualiza
-- colunas). Para um banco já existente, use os dois scripts
-- incrementais que já estão em "Arquivos importantes":
--     1) migracao_produto_estoque.sql   (fornecedor + colunas novas em produto)
--     2) migracao_pedidos.sql           (tabela pedido)
--     3) migracao_nota_fiscal.sql       (preço/valor no pedido + nota fiscal automática)
--
-- As tabelas `fornecedor`, `pedido` e as colunas novas de `produto`
-- (variacao, preco, estoque_qtd, fornecedor_id) eu criei e testei
-- nesta sessão — essa parte eu confirmo com certeza.
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
    preco_unitario    DECIMAL(10,2) NOT NULL DEFAULT 0.00, -- preço do produto NO MOMENTO do pedido
    valor_total       DECIMAL(10,2) NOT NULL DEFAULT 0.00, -- preco_unitario * quantidade
    data_pedido       DATETIME NOT NULL,
    hora_recebimento  TIME NULL,
    nota_fiscal       VARCHAR(60) NULL, -- gerado sozinho pelo sistema (NF-<ano>-<id>), não é digitado
    criado_em         TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_pedido_data (data_pedido),
    CONSTRAINT fk_pedido_produto
        FOREIGN KEY (produto_id) REFERENCES produto(id) ON DELETE SET NULL
);
