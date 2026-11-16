-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 16/11/2026 às 15:10
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `projetorrgi51` (versão limpa: sem estoque, movimento_estoque e venda)
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `cargo`
--

CREATE TABLE `cargo` (
  `id` int(11) NOT NULL,
  `nome` varchar(60) NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `cargo`
--

INSERT INTO `cargo` (`id`, `nome`, `ativo`) VALUES
(1, 'Garçom', 1),
(2, 'Cliente', 1),
(3, 'Degustador dos Produtos', 1),
(4, 'Recepcionista', 1),
(5, 'Chefe de Cozinha', 1),
(6, 'Chefe de Garçòn', 1);

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
-- Estrutura para tabela `empresa`
--

CREATE TABLE `empresa` (
  `id` int(11) NOT NULL DEFAULT 1,
  `cnpj` varchar(14) NOT NULL,
  `razao_social` varchar(150) NOT NULL,
  `lugar` varchar(255) DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `atualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ;

--
-- Despejando dados para a tabela `empresa`
--

INSERT INTO `empresa` (`id`, `cnpj`, `razao_social`, `lugar`, `telefone`, `atualizado_em`) VALUES
(1, '14920407000141', 'SILVIO BERNADINO DE MELO ***16702***', 'VILA BRUNA - SAO PAULO/SP - CEP 02934-000', NULL, '2026-11-16 12:19:02');

-- --------------------------------------------------------

--
-- Estrutura para tabela `entrada_mercadoria`
--

CREATE TABLE `entrada_mercadoria` (
  `id` int(11) NOT NULL,
  `tipo` enum('entrada','venda') NOT NULL DEFAULT 'entrada',
  `produto_id` int(11) DEFAULT NULL,
  `produto_nome` varchar(100) DEFAULT NULL,
  `fornecedor_id` int(11) DEFAULT NULL,
  `pedido_id` int(11) DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `quantidade` int(11) NOT NULL DEFAULT 0,
  `valor_unitario` decimal(10,2) NOT NULL DEFAULT 0.00,
  `estoque_antes` int(11) DEFAULT NULL,
  `estoque_depois` int(11) DEFAULT NULL,
  `data` date NOT NULL,
  `status` enum('rascunho','confirmado') NOT NULL DEFAULT 'rascunho',
  `valor_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `entrada_mercadoria`
--

INSERT INTO `entrada_mercadoria` (`id`, `tipo`, `produto_id`, `produto_nome`, `fornecedor_id`, `pedido_id`, `usuario_id`, `quantidade`, `valor_unitario`, `estoque_antes`, `estoque_depois`, `data`, `status`, `valor_total`, `criado_em`) VALUES
(1, 'entrada', 18, 'Tofu Picante', 8, NULL, 1, 13, 10.00, 148, 161, '2026-11-16', 'confirmado', 130.00, '2026-11-16 12:08:04'),
(2, 'venda', 9, 'Guaracamp', NULL, 1, 13, 1, 50.01, 1000, 999, '2026-11-16', 'confirmado', 50.01, '2026-11-16 12:12:01'),
(3, 'venda', 18, 'Tofu Picante', NULL, 2, 13, 1, 10.00, 161, 160, '2026-11-16', 'confirmado', 10.00, '2026-11-16 12:12:01'),
(4, 'venda', 6, 'Hámburguer Artesanal', NULL, 3, 13, 1, 24.50, 100, 99, '2026-11-16', 'confirmado', 24.50, '2026-11-16 12:12:01'),
(5, 'venda', 14, 'Brigadeirinhos', NULL, 4, 13, 1, 0.50, 1000, 999, '2026-11-16', 'confirmado', 0.50, '2026-11-16 12:12:01'),
(6, 'venda', 9, 'Guaracamp', NULL, 5, 1, 9, 50.01, 999, 990, '2026-11-16', 'confirmado', 450.09, '2026-11-16 13:28:59'),
(7, 'venda', 9, 'Guaracamp', NULL, 6, 1, 37, 50.01, 990, 953, '2026-11-16', 'confirmado', 1850.37, '2026-11-16 13:42:04'),
(8, 'venda', 19, 'Sorvete em copo(300ml)', NULL, 7, 1, 2, 7.80, 385, 383, '2026-11-16', 'confirmado', 15.60, '2026-11-16 13:42:04'),
(9, 'venda', 3, 'Coca Cola', NULL, 8, 1, 2, 0.01, 2, 0, '2026-11-16', 'confirmado', 0.02, '2026-11-16 13:42:04'),
(10, 'venda', 18, 'Tofu Picante', NULL, 9, 1, 24, 10.00, 160, 136, '2026-11-16', 'confirmado', 240.00, '2026-11-16 13:42:04'),
(11, 'venda', 7, 'Churrasco no Espeto', NULL, 10, 1, 25, 25.00, 100, 75, '2026-11-16', 'confirmado', 625.00, '2026-11-16 13:42:04'),
(12, 'venda', 1, 'Frango com Quiabo', NULL, 11, 1, 1, 30.00, 100, 99, '2026-11-16', 'confirmado', 30.00, '2026-11-16 13:42:04');

-- --------------------------------------------------------


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
(6, 'Julinha', '17428727459', 'México', 'Bolo', '2026-09-28', '15:15:54', 1),
(7, 'Forma de Vida Suprema', '719.852.001-42', 'Espaço(Chaos)', 'Carne(Variada)', '2026-09-28', '15:24:01', 1),
(8, 'Ogden Ortiz', '570.214.715-64', 'Ogden\'s Retreat', 'Tofu Picante', '2026-10-05', '16:46:52', 1),
(9, 'Twinkle Slime', NULL, 'Apenas no Natal', 'Musica', '2026-10-05', '16:51:00', 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `funcionario`
--

CREATE TABLE `funcionario` (
  `id` int(11) NOT NULL,
  `nome` varchar(120) NOT NULL,
  `cpf` varchar(20) DEFAULT NULL,
  `lugar` varchar(150) DEFAULT NULL,
  `cargo_id` int(11) DEFAULT NULL,
  `hora_inicio` time DEFAULT NULL,
  `hora_fim` time DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `funcionario`
--

INSERT INTO `funcionario` (`id`, `nome`, `cpf`, `lugar`, `cargo_id`, `hora_inicio`, `hora_fim`, `ativo`) VALUES
(1, 'Lohana', '158795698', 'roblox', 1, '11:30:00', '13:30:00', 1),
(2, 'anna', '46487576965', 'piaui', 3, '12:00:00', '13:00:00', 1),
(3, 'Mimi', '14545454', 'MG- BH', 4, '14:00:00', '17:30:00', 1),
(4, 'bernadete', '40028922', 'Lugar Nenhum', 5, '11:00:00', '21:30:00', 1),
(5, 'dudinea', '548775454875', 'Mato Fino', 2, NULL, NULL, 1),
(6, 'Jaozin', '65887878', 'lasgoa de março', 2, NULL, NULL, 1),
(7, 'camilliki', '55888687.6', 'serrottto', 6, '11:20:00', '15:00:00', 1);

-- --------------------------------------------------------


-- --------------------------------------------------------

--
-- Estrutura para tabela `pedido`
--

CREATE TABLE `pedido` (
  `id` int(11) NOT NULL,
  `produto_id` int(11) DEFAULT NULL,
  `produto_nome` varchar(255) NOT NULL,
  `variacao` varchar(120) DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `quantidade` int(11) NOT NULL DEFAULT 1,
  `preco_unitario` decimal(10,2) NOT NULL DEFAULT 0.00,
  `valor_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `desconto` decimal(10,2) NOT NULL DEFAULT 0.00,
  `data_pedido` datetime NOT NULL,
  `hora_recebimento` time DEFAULT NULL,
  `nota_fiscal` varchar(60) DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `pedido`
--

INSERT INTO `pedido` (`id`, `produto_id`, `produto_nome`, `variacao`, `usuario_id`, `quantidade`, `preco_unitario`, `valor_total`, `desconto`, `data_pedido`, `hora_recebimento`, `nota_fiscal`, `criado_em`) VALUES
(1, 9, 'Guaracamp', NULL, 13, 1, 50.01, 50.01, 0.00, '2026-11-16 13:12:01', NULL, 'NF-2026-000001', '2026-11-16 12:12:01'),
(2, 18, 'Tofu Picante', NULL, 13, 1, 10.00, 10.00, 0.00, '2026-11-16 13:12:01', NULL, 'NF-2026-000001', '2026-11-16 12:12:01'),
(3, 6, 'Hámburguer Artesanal', NULL, 13, 1, 24.50, 24.50, 0.00, '2026-11-16 13:12:01', NULL, 'NF-2026-000001', '2026-11-16 12:12:01'),
(4, 14, 'Brigadeirinhos', NULL, 13, 1, 0.50, 0.50, 0.00, '2026-11-16 13:12:01', NULL, 'NF-2026-000001', '2026-11-16 12:12:01'),
(5, 9, 'Guaracamp', NULL, NULL, 9, 50.01, 450.09, 0.00, '2026-11-16 14:28:00', NULL, 'NF-2026-000005', '2026-11-16 13:28:59'),
(6, 9, 'Guaracamp', NULL, 1, 37, 50.01, 1850.37, 185.04, '2026-11-16 14:42:00', NULL, 'NF-2026-000006', '2026-11-16 13:42:04'),
(7, 19, 'Sorvete em copo(300ml)', NULL, 1, 2, 7.80, 15.60, 1.56, '2026-11-16 14:42:00', NULL, 'NF-2026-000006', '2026-11-16 13:42:04'),
(8, 3, 'Coca Cola', NULL, 1, 2, 0.01, 0.02, 0.00, '2026-11-16 14:42:00', NULL, 'NF-2026-000006', '2026-11-16 13:42:04'),
(9, 18, 'Tofu Picante', NULL, 1, 24, 10.00, 240.00, 24.00, '2026-11-16 14:42:00', NULL, 'NF-2026-000006', '2026-11-16 13:42:04'),
(10, 7, 'Churrasco no Espeto', NULL, 1, 25, 25.00, 625.00, 62.50, '2026-11-16 14:42:00', NULL, 'NF-2026-000006', '2026-11-16 13:42:04'),
(11, 1, 'Frango com Quiabo', NULL, 1, 1, 30.00, 30.00, 3.00, '2026-11-16 14:42:00', NULL, 'NF-2026-000006', '2026-11-16 13:42:04');

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
(1, 1, 'Frango com Quiabo', 'Arroz, Frango, Quiabo, Feijão Tropeiro, Angu', 'Tamanho', 30.00, 99, 7, 1),
(2, 1, 'Frango a milanesa', 'Arroz, Frango empanado, Feijão Preto, Farofa, Salada, Macarrão, Batata Frita', 'Tamanho', 24.50, 200, 7, 1),
(3, 2, 'Coca Cola', 'Coca Cola.', 'Tamanho/Tipos', 0.01, 0, NULL, 1),
(4, 2, 'Água', 'Água sem gás.', 'Água com gás/Água sem gás/Tamanho', 6.00, 200, NULL, 1),
(6, 1, 'Hámburguer Artesanal', 'Pão, Carne, Queijo, Alface, Tomate, Ketchup.', 'Ingredientes', 24.50, 99, 7, 1),
(7, 1, 'Churrasco no Espeto', 'Coração, Carne(Picanha) e Linguiça no espeto + Arroz com farofa e Molho á Campanha em pote separado.', 'Tipo/Tamanho', 25.00, 75, 7, 1),
(8, 2, 'Guaraná', 'Guaraná Antártica.', 'Tamanho', 8.50, 1500, NULL, 1),
(9, 2, 'Guaracamp', 'Guaraná Natural: Guaracamp.', 'Sabores', 50.01, 953, NULL, 1),
(11, 3, 'Bolo de Pote', 'Bolo no Pote Sabor: Chocolate.', 'Tamanho/Com Recheio/Sem Recheio', 13.50, 300, 6, 1),
(13, 3, 'Bolo de Pote', 'Bolo no Pote Sabor: Maracujá\r\n                                                                                 .', 'Com recheio/Sem recheio', 3.50, 300, 6, 1),
(14, 3, 'Brigadeirinhos', 'Brigadeirinhos pequenos. Qtd: 6.', NULL, 0.50, 999, 4, 1),
(17, 3, 'Bolo de Pote', 'Bolo de Pote sabor Morango', 'Tamanho/Com Recheio/Sem Recheio', 3.50, 300, 6, 1),
(18, 1, 'Tofu Picante', 'Nunca terminará de te encher!', NULL, 10.00, 136, 8, 1),
(19, 3, 'Sorvete em copo(300ml)', 'Sorvete de morango em copo', NULL, 7.80, 383, 4, 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `produto_variacao`
--

CREATE TABLE `produto_variacao` (
  `id` int(11) NOT NULL,
  `produto_id` int(11) NOT NULL,
  `nome` varchar(60) NOT NULL,
  `preco` decimal(10,2) NOT NULL DEFAULT 0.00,
  `ativo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(9, 'ASD', 'pcmedio19@gmail.com', '$2y$10$MQLzReBfNTkUqlWL8.H7M.6h9lk7BWvqaKjC0fpxMy76k6EsAEHQO', 'cliente', 1),
(10, 'assd', 'pcmedio199@gmail.com', '$2y$10$Y7YlrRE7rP0CXOsQgTT0nOPrlYdXO1HHa5hzB4Vy0Jc3bFeG5IKri', 'cliente', 1),
(11, 'joeol', 'rogerio123@gmail.com', '$2y$10$Pg/nXAeCRjxl4jXAQCJf0uOAlNcp6xnM9gBftCXsDCGt3OmC0/dI6', 'cliente', 1),
(12, 'jossa', 'pcmedio09@3dcolegios.com', '$2y$10$y7UmpOZIyvJi2DqyTjUbJu.XzBQhWEtQWH0EFXugk.U.vSqYwONPK', 'cliente', 1),
(13, 'Lohana', 'loh@gmail.com', '$2y$10$ZyS6a0TsQu8Is7R02E79Ru41SP1Qd.gGsmi1mp88QpeOj7/8qZ/l6', 'cliente', 1);

-- --------------------------------------------------------


--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `cargo`
--
ALTER TABLE `cargo`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_cargo_nome` (`nome`);

--
-- Índices de tabela `categoria1`
--
ALTER TABLE `categoria1`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `empresa`
--
ALTER TABLE `empresa`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `entrada_mercadoria`
--
ALTER TABLE `entrada_mercadoria`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_entrada_data` (`data`),
  ADD KEY `idx_entrada_status` (`status`),
  ADD KEY `fk_mov_produto` (`produto_id`),
  ADD KEY `fk_mov_fornecedor` (`fornecedor_id`),
  ADD KEY `fk_mov_pedido` (`pedido_id`),
  ADD KEY `fk_mov_usuario` (`usuario_id`);


--
-- Índices de tabela `fornecedor`
--
ALTER TABLE `fornecedor`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `funcionario`
--
ALTER TABLE `funcionario`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_funcionario_cargo` (`cargo_id`);


--
-- Índices de tabela `pedido`
--
ALTER TABLE `pedido`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pedido_data` (`data_pedido`),
  ADD KEY `fk_pedido_produto` (`produto_id`),
  ADD KEY `fk_pedido_usuario` (`usuario_id`);

--
-- Índices de tabela `produto`
--
ALTER TABLE `produto`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_produto_categoria` (`categoria_id`),
  ADD KEY `fk_produto_fornecedor` (`fornecedor_id`);

--
-- Índices de tabela `produto_variacao`
--
ALTER TABLE `produto_variacao`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_variacao_produto` (`produto_id`);

--
-- Índices de tabela `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);


--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `cargo`
--
ALTER TABLE `cargo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `categoria1`
--
ALTER TABLE `categoria1`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `entrada_mercadoria`
--
ALTER TABLE `entrada_mercadoria`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;


--
-- AUTO_INCREMENT de tabela `fornecedor`
--
ALTER TABLE `fornecedor`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de tabela `funcionario`
--
ALTER TABLE `funcionario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;


--
-- AUTO_INCREMENT de tabela `pedido`
--
ALTER TABLE `pedido`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de tabela `produto`
--
ALTER TABLE `produto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de tabela `produto_variacao`
--
ALTER TABLE `produto_variacao`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;


--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `entrada_mercadoria`
--
ALTER TABLE `entrada_mercadoria`
  ADD CONSTRAINT `fk_mov_fornecedor` FOREIGN KEY (`fornecedor_id`) REFERENCES `fornecedor` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_mov_pedido` FOREIGN KEY (`pedido_id`) REFERENCES `pedido` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_mov_produto` FOREIGN KEY (`produto_id`) REFERENCES `produto` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_mov_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `funcionario`
--
ALTER TABLE `funcionario`
  ADD CONSTRAINT `fk_funcionario_cargo` FOREIGN KEY (`cargo_id`) REFERENCES `cargo` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `pedido`
--
ALTER TABLE `pedido`
  ADD CONSTRAINT `fk_pedido_produto` FOREIGN KEY (`produto_id`) REFERENCES `produto` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_pedido_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `produto`
--
ALTER TABLE `produto`
  ADD CONSTRAINT `fk_produto_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `categoria1` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_produto_fornecedor` FOREIGN KEY (`fornecedor_id`) REFERENCES `fornecedor` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `produto_variacao`
--
ALTER TABLE `produto_variacao`
  ADD CONSTRAINT `fk_variacao_produto` FOREIGN KEY (`produto_id`) REFERENCES `produto` (`id`) ON DELETE CASCADE;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
