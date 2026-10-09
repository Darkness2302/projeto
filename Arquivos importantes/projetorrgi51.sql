CREATE DATABASE IF NOT EXISTS projetorrgi51 CHARACTER SET utf8mb4;
USE projetorrgi51;

-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 05/10/2026 às 13:26
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `projetorrgi51`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `categoria1`
--

CREATE TABLE `categoria1` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `categoria1`
--

INSERT INTO `categoria1` (`id`, `nome`, `ativo`) VALUES
(1, 'Comidas', 1),
(2, 'Bebidas', 1),
(3, 'Sobremesas', 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `entrada_item`
--

CREATE TABLE `entrada_item` (
  `id` int(11) NOT NULL,
  `entrada_id` int(11) NOT NULL,
  `variacao_id` int(11) NOT NULL,
  `quantidade` int(11) NOT NULL,
  `custo_unitario` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `entrada_mercadoria`
--

CREATE TABLE `entrada_mercadoria` (
  `id` int(11) NOT NULL,
  `data` date NOT NULL,
  `status` enum('rascunho','confirmado') NOT NULL DEFAULT 'rascunho',
  `valor_total` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `estoque`
--

CREATE TABLE `estoque` (
  `id` int(11) NOT NULL,
  `variacao_id` int(11) NOT NULL,
  `quantidade` int(11) NOT NULL DEFAULT 0,
  `minimo` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `fornecedor`
--

CREATE TABLE `fornecedor` (
  `id` int(11) NOT NULL,
  `nome` varchar(120) NOT NULL,
  `cpf` varchar(20) DEFAULT NULL,
  `lugar` varchar(150) DEFAULT NULL,
  `produtos` text DEFAULT NULL,
  `data_recebimento` date DEFAULT NULL,
  `hora_recebimento` time DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `fornecedor`
--

INSERT INTO `fornecedor` (`id`, `nome`, `cpf`, `lugar`, `produtos`, `data_recebimento`, `hora_recebimento`, `ativo`) VALUES
(1, 'Jussçãra', '40028922', 'Horti-Fruti da Dona Florenza', 'Frutas', '2026-09-28', '14:12:24', 1),
(2, 'Percí', '10020058025', 'São Paulo', 'Fruta', '2026-09-28', '15:18:30', 1),
(3, 'Mao Mao', '2156213562365', 'China', 'Tempero', '2026-09-28', '15:00:13', 1),
(4, 'Vaneloip', '5522162522352', 'Groelândia', 'Doces', '2026-09-28', '15:04:31', 1),
(5, 'Quengar', '71717171717171717171', 'Cabuçu', 'Bebidas', '2026-09-28', '15:11:06', 1),
(6, 'Julinha', '17428727459', 'México', 'Bolo', '2026-09-28', '15:15:54', 1),
(7, 'Forma de Vida Suprema', '719.852.001-42', 'Espaço(Chaos)', 'Carne(Variada)', '2026-09-28', '15:24:01', 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `movimento_estoque`
--

CREATE TABLE `movimento_estoque` (
  `id` int(11) NOT NULL,
  `variacao_id` int(11) NOT NULL,
  `tipo` enum('entrada','venda') NOT NULL,
  `quantidade` int(11) NOT NULL,
  `origem` enum('entrada','saida') NOT NULL,
  `origem_id` int(11) NOT NULL,
  `data` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `produto`
--

CREATE TABLE `produto` (
  `id` int(11) NOT NULL,
  `categoria_id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `descricao` text DEFAULT NULL,
  `variacao` varchar(150) DEFAULT NULL,
  `preco` decimal(10,2) NOT NULL DEFAULT 0.00,
  `estoque_qtd` int(11) NOT NULL DEFAULT 0,
  `fornecedor_id` int(11) DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `produto`
--

INSERT INTO `produto` (`id`, `categoria_id`, `nome`, `descricao`, `variacao`, `preco`, `estoque_qtd`, `fornecedor_id`, `ativo`) VALUES
(1, 1, 'Frango com Quiabo', 'Arroz, Frango, Quiabo, Feijão Tropeiro, Angu', 'Tamanho', 30.00, 100, 7, 1),
(2, 1, 'Frango a milanesa', 'Arroz, Frango empanado, Feijão Preto, Farofa, Salada, Macarrão, Batata Frita', 'Tamanho', 24.50, 200, 7, 1),
(3, 2, 'Coca Cola', 'Coca Cola.', 'Tamanho/Tipos', 7.50, 300, 5, 1),
(4, 2, 'Água', 'Água sem gás.', 'Água com gás/Água sem gás/Tamanho', 6.00, 200, 5, 1),
(6, 1, 'Hámburguer Artesanal', 'Pão, Carne, Queijo, Alface, Tomate, Ketchup.', 'Ingredientes', 24.50, 100, 7, 1),
(7, 1, 'Churrasco no Espeto', 'Coração, Carne(Picanha) e Linguiça no espeto + Arroz com farofa e Molho á Campanha em pote separado.', 'Tipo/Tamanho', 25.00, 100, 7, 1),
(8, 2, 'Guaraná', 'Guaraná Antártica.', 'Tamanho', 8.50, 1500, 5, 1),
(9, 2, 'Guaracamp', 'Guaraná Natural: Guaracamp.', 'Sabores', 50.01, 1000, 5, 1),
(11, 3, 'Bolo de Pote', 'Bolo no Pote Sabor: Chocolate.', 'Tamanho/Com Recheio/Sem Recheio', 13.50, 300, 6, 1),
(13, 3, 'Bolo de Pote', 'Bolo no Pote Sabor: Maracujá\r\n                                                                                 .', 'Com recheio/Sem recheio', 3.50, 300, 6, 1),
(14, 3, 'Brigadeirinhos', 'Brigadeirinhos pequenos. Qtd: 6.', NULL, 0.50, 1000, 4, 1),
(17, 3, 'Bolo de Pote', 'Bolo de Pote sabor Morango', 'Tamanho/Com Recheio/Sem Recheio', 3.50, 300, 6, 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuario`
--

CREATE TABLE `usuario` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `perfil` enum('gerente','garçom','cliente') NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuario`
--

INSERT INTO `usuario` (`id`, `nome`, `email`, `senha`, `perfil`, `ativo`) VALUES
(1, 'Admin', 'admin@restaurante.com', '$2y$10$vy99TRldnH7xIy6/at451eBFtrWONTnEup3KRKxP9LNBrWS/WnuLm', 'gerente', 1),
(2, 'Chefe Mario', 'chefe@comida.com', '$2y$10$V7a54RYRJUUjJrciW9KEa.b120fv4NRTXj1KN0FiPpjH2m2zoQisu', 'gerente', 1),
(3, 'cleitin', 'pcmedio9@3dcolegios.com', '$2y$10$ltpTrG3AlO6haZXCLMdVG.tUYQt1JhoHi.8R2cBPxoj9iaKl6yDQW', 'garçom', 1),
(4, 'john cena', 'john.cena@gmail.com', '$2y$10$IDstDwfS/Xu626Yobo7vbuxyHErEfg..nL8LALZkvieCj6WIwuzBS', 'garçom', 1),
(8, 'Joh Pork', 'pcmedio09@gmail.com', '$2y$10$qMdJ8z1370CcCWZWvbZ3DORdQGTJKAxX7qonh5wsy8i0Jd4Uqj0k2', 'cliente', 1),
(9, 'ASD', 'pcmedio19@gmail.com', '$2y$10$MQLzReBfNTkUqlWL8.H7M.6h9lk7BWvqaKjC0fpxMy76k6EsAEHQO', 'cliente', 1),
(10, 'assd', 'pcmedio199@gmail.com', '$2y$10$Y7YlrRE7rP0CXOsQgTT0nOPrlYdXO1HHa5hzB4Vy0Jc3bFeG5IKri', 'cliente', 1),
(11, 'joeol', 'rogerio123@gmail.com', '$2y$10$Pg/nXAeCRjxl4jXAQCJf0uOAlNcp6xnM9gBftCXsDCGt3OmC0/dI6', 'cliente', 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `venda`
--

CREATE TABLE `venda` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `cliente_id` int(11) DEFAULT NULL,
  `data` date NOT NULL,
  `status` enum('aberta','finalizada') NOT NULL DEFAULT 'aberta',
  `valor_total` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `venda_item`
--

CREATE TABLE `venda_item` (
  `id` int(11) NOT NULL,
  `venda_id` int(11) NOT NULL,
  `variacao_id` int(11) NOT NULL,
  `quantidade` int(11) NOT NULL,
  `preco_unitario` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `categoria1`
--
ALTER TABLE `categoria1`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `entrada_item`
--
ALTER TABLE `entrada_item`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_entrada_item_entrada` (`entrada_id`),
  ADD KEY `idx_entrada_item_variacao` (`variacao_id`);

--
-- Índices de tabela `entrada_mercadoria`
--
ALTER TABLE `entrada_mercadoria`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_entrada_data` (`data`),
  ADD KEY `idx_entrada_status` (`status`);

--
-- Índices de tabela `estoque`
--
ALTER TABLE `estoque`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `variacao_id` (`variacao_id`);

--
-- Índices de tabela `fornecedor`
--
ALTER TABLE `fornecedor`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `movimento_estoque`
--
ALTER TABLE `movimento_estoque`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_mov_variacao` (`variacao_id`),
  ADD KEY `idx_mov_data` (`data`),
  ADD KEY `idx_mov_origem` (`origem`,`origem_id`),
  ADD KEY `idx_mov_tipo` (`tipo`);

--
-- Índices de tabela `produto`
--
ALTER TABLE `produto`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_produto_categoria` (`categoria_id`),
  ADD KEY `fk_produto_fornecedor` (`fornecedor_id`);

--
-- Índices de tabela `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Índices de tabela `venda`
--
ALTER TABLE `venda`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_venda_usuario` (`usuario_id`),
  ADD KEY `idx_venda_cliente` (`cliente_id`),
  ADD KEY `idx_venda_data` (`data`),
  ADD KEY `idx_venda_status` (`status`);

--
-- Índices de tabela `venda_item`
--
ALTER TABLE `venda_item`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_venda_item_venda` (`venda_id`),
  ADD KEY `idx_venda_item_variacao` (`variacao_id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `categoria1`
--
ALTER TABLE `categoria1`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `entrada_item`
--
ALTER TABLE `entrada_item`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `entrada_mercadoria`
--
ALTER TABLE `entrada_mercadoria`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `estoque`
--
ALTER TABLE `estoque`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `fornecedor`
--
ALTER TABLE `fornecedor`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `movimento_estoque`
--
ALTER TABLE `movimento_estoque`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `produto`
--
ALTER TABLE `produto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de tabela `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de tabela `venda`
--
ALTER TABLE `venda`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `venda_item`
--
ALTER TABLE `venda_item`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `entrada_item`
--
ALTER TABLE `entrada_item`
  ADD CONSTRAINT `fk_entrada_item_entrada` FOREIGN KEY (`entrada_id`) REFERENCES `entrada_mercadoria` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `produto`
--
ALTER TABLE `produto`
  ADD CONSTRAINT `fk_produto_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `categoria1` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_produto_fornecedor` FOREIGN KEY (`fornecedor_id`) REFERENCES `fornecedor` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `venda`
--
ALTER TABLE `venda`
  ADD CONSTRAINT `fk_venda_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;


-- ============================================================
-- MIGRAÇÃO: Pedidos (histórico)
-- ============================================================
-- Migração: Pedidos (histórico) — Figma "Página Pedidos" (2:97)
-- Execute UMA VEZ no banco `projetorrgi51`.
-- Pré-requisito: rodar antes o script migracao_produto_estoque.sql
-- (a tela de pedidos lista os produtos ativos).

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

-- ============================================================
-- MIGRAÇÃO: Nota fiscal automática
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

-- ============================================================
-- MIGRAÇÃO: Empresa (CNPJ da nota fiscal)
-- ============================================================
CREATE TABLE IF NOT EXISTS empresa (
    id            INT NOT NULL PRIMARY KEY DEFAULT 1,
    cnpj          VARCHAR(14) NOT NULL,          -- só dígitos, sem máscara
    razao_social  VARCHAR(150) NOT NULL,
    lugar         VARCHAR(255) NULL,              -- preenchido pela busca do CNPJ, editável
    telefone      VARCHAR(20) NULL,
    atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT chk_empresa_id_unico CHECK (id = 1)
);

-- MIGRAÇÃO: Funcionários (garçons) e Cargos
-- 1) Cargos (criados pelo gerente na própria tela de Funcionários)
CREATE TABLE IF NOT EXISTS cargo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(60) NOT NULL,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    UNIQUE KEY uq_cargo_nome (nome)
);

INSERT IGNORE INTO cargo (nome) VALUES ('Garçom'), ('Cliente');

-- 2) Funcionários (mesmo padrão de colunas da tabela fornecedor)
CREATE TABLE IF NOT EXISTS funcionario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(120) NOT NULL,
    cpf VARCHAR(20) NULL,
    lugar VARCHAR(150) NULL,
    hora_inicio TIME NULL,              -- início do expediente
    hora_fim TIME NULL,                 -- fim do expediente
    ativo TINYINT(1) NOT NULL DEFAULT 1
);

-- 3) Ajustes para bancos que já tinham a versão anterior
ALTER TABLE funcionario ADD COLUMN IF NOT EXISTS hora_inicio TIME NULL;
ALTER TABLE funcionario ADD COLUMN IF NOT EXISTS hora_fim TIME NULL;
ALTER TABLE funcionario DROP COLUMN IF EXISTS data_recebimento;
ALTER TABLE funcionario DROP COLUMN IF EXISTS hora_recebimento;
ALTER TABLE funcionario DROP COLUMN IF EXISTS produtos;

-- 4) Cargo do funcionário (apaga o cargo -> funcionário fica sem cargo)
ALTER TABLE funcionario ADD COLUMN IF NOT EXISTS cargo_id INT NULL AFTER lugar;
ALTER TABLE funcionario
    ADD CONSTRAINT fk_funcionario_cargo
    FOREIGN KEY (cargo_id) REFERENCES cargo(id)
    ON DELETE SET NULL;

-- MIGRAÇÃO: Opções de variação
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

-- MIGRAÇÃO: Pedidos feitos pelo cliente
ALTER TABLE pedido ADD COLUMN IF NOT EXISTS usuario_id INT NULL AFTER variacao;

ALTER TABLE pedido
    ADD CONSTRAINT fk_pedido_usuario
    FOREIGN KEY (usuario_id) REFERENCES usuario(id)
    ON DELETE SET NULL;

-- MIGRAÇÃO: Desconto de 10% (7 itens ou mais)
ALTER TABLE pedido ADD COLUMN IF NOT EXISTS desconto DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER valor_total;
