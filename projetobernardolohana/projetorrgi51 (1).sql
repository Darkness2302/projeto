-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 30/10/2026 às 14:16
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
(6, 'Julinha', '17428727459', 'México', 'Bolo', '2026-09-28', '15:15:54', 1),
(7, 'Forma de Vida Suprema', '719.852.001-42', 'Espaço(Chaos)', 'Carne(Variada)', '2026-09-28', '15:24:01', 1),
(8, 'Ogden Ortiz', '570.214.715-64', 'Ogden\'s Retreat', 'Tofu Picante', '2026-10-05', '16:46:52', 1),
(9, 'Twinkle Slime', NULL, 'Apenas no Natal', 'Musica', '2026-10-05', '16:51:51', 1);

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
(3, 2, 'Coca Cola', 'Coca Cola.', 'Tamanho/Tipos', 0.01, 2, NULL, 1),
(4, 2, 'Água', 'Água sem gás.', 'Água com gás/Água sem gás/Tamanho', 6.00, 200, NULL, 1),
(6, 1, 'Hámburguer Artesanal', 'Pão, Carne, Queijo, Alface, Tomate, Ketchup.', 'Ingredientes', 24.50, 100, 7, 1),
(7, 1, 'Churrasco no Espeto', 'Coração, Carne(Picanha) e Linguiça no espeto + Arroz com farofa e Molho á Campanha em pote separado.', 'Tipo/Tamanho', 25.00, 100, 7, 1),
(8, 2, 'Guaraná', 'Guaraná Antártica.', 'Tamanho', 8.50, 1500, NULL, 1),
(9, 2, 'Guaracamp', 'Guaraná Natural: Guaracamp.', 'Sabores', 50.01, 1000, NULL, 1),
(11, 3, 'Bolo de Pote', 'Bolo no Pote Sabor: Chocolate.', 'Tamanho/Com Recheio/Sem Recheio', 13.50, 300, 6, 1),
(13, 3, 'Bolo de Pote', 'Bolo no Pote Sabor: Maracujá\r\n                                                                                 .', 'Com recheio/Sem recheio', 3.50, 300, 6, 1),
(14, 3, 'Brigadeirinhos', 'Brigadeirinhos pequenos. Qtd: 6.', NULL, 0.50, 1000, 4, 1),
(17, 3, 'Bolo de Pote', 'Bolo de Pote sabor Morango', 'Tamanho/Com Recheio/Sem Recheio', 3.50, 300, 6, 1),
(18, 1, 'Tofu Picante', 'Nunca terminará de te encher!', NULL, 10.00, 148, 8, 1);

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

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `categoria1`
--
ALTER TABLE `categoria1`
  ADD PRIMARY KEY (`id`);

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
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `categoria1`
--
ALTER TABLE `categoria1`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de tabela `movimento_estoque`
--
ALTER TABLE `movimento_estoque`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `produto`
--
ALTER TABLE `produto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de tabela `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de tabela `venda`
--
ALTER TABLE `venda`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restrições para tabelas despejadas
--

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
