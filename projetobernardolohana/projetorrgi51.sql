-- ============================================================
-- Panela Quente — projetorrgi51_adaptado.sql
-- Baseado no dump real que você enviou (projetorrgi51.sql),
-- CORRIGIDO + COMPLETADO + com as mudanças desta sessão aplicadas.
-- ============================================================
--
-- ⚠️ LEIA ISTO ANTES DE RODAR — achei problemas no arquivo original
-- que não têm nada a ver com o que eu mudei; provavelmente a
-- exportação do phpMyAdmin não pegou tudo.
--
-- 1) BUG DE SINTAXE (o arquivo original nem importa por causa disso):
--    `perfil` enum('gerente','garçom',) — vírgula sobrando antes do
--    fechamento. Corrigido, e adicionei 'cliente' como valor válido
--    (sem isso, todo autocadastro do site ia falhar silenciosamente,
--    porque o app grava perfil='cliente' e esse valor nem existia
--    na lista do enum).
--
-- 2) BUG DE CÓDIGO que eu também já corrigi no PHP desta sessão:
--    seus usuários reais estão salvos com perfil = 'garçom' (com
--    cedilha), mas meu código (helpers/acl.php e os controllers de
--    produto/venda) estava comparando com 'garcom' sem cedilha —
--    ou seja, nenhum garçom de verdade conseguiria entrar em Estoque
--    nem Pedidos. Já troquei as 8 ocorrências no projeto pra
--    'garçom' com cedilha, batendo com o que está no seu banco.
--
-- 3) TRÊS TABELAS REFERENCIADAS MAS QUE NÃO ESTAVAM NO ARQUIVO:
--    `variacao`, `fornecedor` e `cliente` são usadas em chaves
--    estrangeiras e índices (ex.: fk_variacao_produto, fk_entrada_fornecedor,
--    fk_venda_cliente) mas nunca aparecem com CREATE TABLE — o mais
--    provável é que a exportação do phpMyAdmin esqueceu de marcar
--    essas 3 tabelas. Eu RECONSTRUÍ a estrutura mínima de cada uma
--    usando só o que dava pra confirmar pelas referências existentes
--    (colunas que aparecem em índice/FK). Estão marcadas abaixo com
--    "RECONSTRUÍDA" — se a tabela real tiver mais colunas que isso
--    no seu banco, me manda o CREATE TABLE dela que eu ajusto.
--
-- 4) COLUNAS FALTANDO em tabelas que existiam (usadas em índice/FK
--    mas nunca declaradas na CREATE TABLE): `entrada_item.variacao_id`,
--    `entrada_mercadoria.fornecedor_id`, `estoque.variacao_id`,
--    `movimento_estoque.variacao_id`. Adicionei as 4.
--
-- 5) PROVÁVEL ERRO DE DIGITAÇÃO: a constraint fk_venda_item_variacao
--    original referenciava `venda_item.id` (a própria chave primária)
--    em vez de `venda_item.variacao_id` — do jeito que estava, não
--    fazia sentido (e o índice idx_venda_item_variacao já existia em
--    cima de variacao_id, então é claramente isso que era pra ser).
--    Corrigido.
--
-- 6) ARQUITETURA DIVERGENTE — isto eu NÃO resolvi sozinho, preciso
--    da sua decisão:
--    Seu banco real já tinha um desenho de estoque bem mais
--    elaborado do que o que eu fui construindo nas sessões: preço e
--    quantidade ficam por VARIAÇÃO do produto (tabela `variacao` +
--    `estoque` por variacao_id + `movimento_estoque` como
--    auditoria + `entrada_mercadoria`/`entrada_item` pra recebimento
--    de mercadoria). Só que meu código PHP (views/produto_form.php,
--    controllers/produtocontroller.php) NUNCA usa essas tabelas —
--    ele lê e grava preço/estoque direto em `produto.preco` e
--    `produto.estoque_qtd`, que foram colunas que eu adicionei sem
--    saber que esse desenho por variação já existia.
--    Da mesma forma, `views/vendas.php` grava pedidos numa tabela
--    `pedido` simples que eu criei — e não nas tabelas `venda` +
--    `venda_item` que já existiam no seu banco com essa exata
--    finalidade (cabeçalho do pedido + itens).
--    Ou seja: hoje convivem DOIS sistemas de estoque e DOIS sistemas
--    de pedido no mesmo banco, sem se falar. Este arquivo mantém os
--    dois de pé (nada quebra, nada se perde), mas isso não deveria
--    ficar assim — em algum momento um dos dois lados vai precisar
--    ser adaptado pro outro. Me avisa qual caminho você quer:
--      (a) jogar fora o desenho por variação e ficar só com
--          produto.preco/estoque_qtd + tabela pedido (mais simples,
--          é o que já está funcionando hoje), ou
--      (b) eu reescrevo produto_form.php/produtocontroller.php/
--          vendas.php pra usar de verdade variacao/estoque/
--          movimento_estoque/venda/venda_item (mais parecido com o
--          que você já tinha desenhado, mais trabalho de código).
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS projetorrgi51 CHARACTER SET utf8mb4;
USE projetorrgi51;

-- ------------------------------------------------------------
-- categoria1  (igual ao seu dump original, com seus dados reais)
-- ------------------------------------------------------------
CREATE TABLE `categoria1` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `categoria1` (`id`, `nome`, `ativo`) VALUES
(1, 'Comidas', 1),
(2, 'Bebidas', 1),
(3, 'Sobremesas', 1);

-- ------------------------------------------------------------
-- fornecedor  (RECONSTRUÍDA — não existia no arquivo, mas era
-- referenciada por entrada_mercadoria.fornecedor_id. Estrutura vem
-- do módulo de Fornecedores que criei nesta sessão; supre as duas
-- necessidades ao mesmo tempo. Não confundir com o perfil de
-- usuário "garçom" — são conceitos diferentes.)
-- ------------------------------------------------------------
CREATE TABLE `fornecedor` (
  `id` int(11) NOT NULL,
  `nome` varchar(150) NOT NULL,
  `cpf` varchar(20) DEFAULT NULL,
  `lugar` varchar(150) DEFAULT NULL,
  `produtos` varchar(255) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `data_recebimento` date DEFAULT NULL,
  `hora_recebimento` time DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- cliente  (RECONSTRUÍDA — não existia no arquivo, mas era
-- referenciada por venda.cliente_id. Estrutura mínima: só o que dá
-- pra confirmar que existe — pode ter mais colunas no seu banco.)
-- ------------------------------------------------------------
CREATE TABLE `cliente` (
  `id` int(11) NOT NULL,
  `nome` varchar(150) NOT NULL,
  `telefone` varchar(30) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- produto  (igual ao seu dump original, com seus 14 produtos reais,
-- + as colunas que esta sessão adicionou: variacao, preco,
-- estoque_qtd, fornecedor_id — ver nota 6 acima sobre elas
-- coexistirem com a tabela `variacao` abaixo)
-- ------------------------------------------------------------
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

INSERT INTO `produto` (`id`, `categoria_id`, `nome`, `descricao`, `ativo`) VALUES
(1, 1, 'Frango com Quiabo', 'Arroz, Frango, Quiabo, Feijão Tropeiro, Angu', 1),
(2, 1, 'Frango a milanesa', 'Arroz, Frango empanado, Feijão Preto, Farofa, Salada, Macarrão, Batata Frita', 1),
(3, 2, 'Coca Cola', 'Coca Cola.', 1),
(4, 2, 'Água', 'Água sem gás.', 1),
(5, 2, 'Água', 'Água com gás.', 1),
(6, 1, 'Hámburguer Artesanal', 'Pão, Carne, Queijo, Alface, Tomate, Ketchup.', 1),
(7, 1, 'Churrasco no Espeto', 'Coração, Carne(Picanha) e Linguiça no espeto + Arroz com farofa e Molho á Campanha em pote separado.', 1),
(8, 2, 'Guaraná', 'Guaraná Antártica.', 1),
(9, 2, 'Guaraná', 'Guaraná Natural: Guaracamp.', 1),
(10, 2, 'Coca Cola', 'Coca Cola sem açucar.', 1),
(11, 3, 'Bolo de Pote', 'Bolo no Pote Sabor: Chocolate.', 1),
(12, 3, 'Bolo de Pote', 'Bolo no Pote Sabor: Morango\r\n                                                                                 .', 1),
(13, 3, 'Bolo de Pote', 'Bolo no Pote Sabor: Maracujá\r\n                                                                                 .', 1),
(14, 3, 'Brigadeirinhos', 'Brigadeirinhos pequenos. Qtd: 6.', 1);

-- ------------------------------------------------------------
-- variacao  (RECONSTRUÍDA — não existia no arquivo, mas era
-- referenciada por entrada_item, estoque, movimento_estoque e
-- venda_item. Estrutura mínima inferida: precisa de produto_id
-- [confirmado pelo índice fk_variacao_produto] e nome + preço pra
-- fazer sentido como "variação vendável" de um produto.)
-- ------------------------------------------------------------
CREATE TABLE `variacao` (
  `id` int(11) NOT NULL,
  `produto_id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `preco` decimal(10,2) NOT NULL DEFAULT 0.00,
  `ativo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- estoque  (igual ao original + coluna variacao_id, que faltava)
-- ------------------------------------------------------------
CREATE TABLE `estoque` (
  `id` int(11) NOT NULL,
  `variacao_id` int(11) NOT NULL,
  `quantidade` int(11) NOT NULL DEFAULT 0,
  `minimo` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- movimento_estoque  (igual ao original + coluna variacao_id)
-- ------------------------------------------------------------
CREATE TABLE `movimento_estoque` (
  `id` int(11) NOT NULL,
  `variacao_id` int(11) NOT NULL,
  `tipo` enum('entrada','venda') NOT NULL,
  `quantidade` int(11) NOT NULL,
  `origem` enum('entrada','saida') NOT NULL,
  `origem_id` int(11) NOT NULL,
  `data` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- entrada_mercadoria  (igual ao original + coluna fornecedor_id)
-- ------------------------------------------------------------
CREATE TABLE `entrada_mercadoria` (
  `id` int(11) NOT NULL,
  `fornecedor_id` int(11) DEFAULT NULL,
  `data` date NOT NULL,
  `status` enum('rascunho','confirmado') NOT NULL DEFAULT 'rascunho',
  `valor_total` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- entrada_item  (igual ao original + coluna variacao_id)
-- ------------------------------------------------------------
CREATE TABLE `entrada_item` (
  `id` int(11) NOT NULL,
  `entrada_id` int(11) NOT NULL,
  `variacao_id` int(11) NOT NULL,
  `quantidade` int(11) NOT NULL,
  `custo_unitario` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- pedido  (NOVA desta sessão — histórico simples usado hoje por
-- views/vendas.php. Ver nota 6 acima sobre a sobreposição com
-- venda/venda_item.)
-- ------------------------------------------------------------
CREATE TABLE `pedido` (
  `id` int(11) NOT NULL,
  `produto_id` int(11) DEFAULT NULL,
  `produto_nome` varchar(255) NOT NULL,
  `quantidade` int(11) NOT NULL DEFAULT 1,
  `data_pedido` datetime NOT NULL,
  `hora_recebimento` time DEFAULT NULL,
  `nota_fiscal` varchar(60) DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- usuario  (igual ao original, com seus 6 usuários reais — só o
-- enum corrigido: sem a vírgula sobrando, e com 'cliente' incluído)
-- ------------------------------------------------------------
CREATE TABLE `usuario` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `perfil` enum('cliente','garçom','gerente') NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `usuario` (`id`, `nome`, `email`, `senha`, `perfil`, `ativo`) VALUES
(1, 'Admin', 'admin@restaurante.com', '$2y$10$vy99TRldnH7xIy6/at451eBFtrWONTnEup3KRKxP9LNBrWS/WnuLm', 'gerente', 1),
(2, 'Chefe Mario', 'chefe@comida.com', '$2y$10$V7a54RYRJUUjJrciW9KEa.b120fv4NRTXj1KN0FiPpjH2m2zoQisu', 'gerente', 1),
(3, 'cleitin', 'pcmedio9@3dcolegios.com', '$2y$10$ltpTrG3AlO6haZXCLMdVG.tUYQt1JhoHi.8R2cBPxoj9iaKl6yDQW', 'garçom', 1),
(4, 'john cena', 'john.cena@gmail.com', '$2y$10$IDstDwfS/Xu626Yobo7vbuxyHErEfg..nL8LALZkvieCj6WIwuzBS', 'garçom', 1),
(8, 'Joh Pork', 'pcmedio09@gmail.com', '$2y$10$qMdJ8z1370CcCWZWvbZ3DORdQGTJKAxX7qonh5wsy8i0Jd4Uqj0k2', 'garçom', 1),
(9, 'ASD', 'pcmedio19@gmail.com', '$2y$10$MQLzReBfNTkUqlWL8.H7M.6h9lk7BWvqaKjC0fpxMy76k6EsAEHQO', 'garçom', 1),
(10, 'assd', 'pcmedio199@gmail.com', '$2y$10$Y7YlrRE7rP0CXOsQgTT0nOPrlYdXO1HHa5hzB4Vy0Jc3bFeG5IKri', 'garçom', 1);

-- ------------------------------------------------------------
-- venda / venda_item  (iguais ao original — sistema de pedido que já
-- existia no seu banco; ver nota 6 acima)
-- ------------------------------------------------------------
CREATE TABLE `venda` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `cliente_id` int(11) DEFAULT NULL,
  `data` date NOT NULL,
  `status` enum('aberta','finalizada') NOT NULL DEFAULT 'aberta',
  `valor_total` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `venda_item` (
  `id` int(11) NOT NULL,
  `venda_id` int(11) NOT NULL,
  `variacao_id` int(11) NOT NULL,
  `quantidade` int(11) NOT NULL,
  `preco_unitatio` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================
-- Índices
-- ============================================================
ALTER TABLE `categoria1` ADD PRIMARY KEY (`id`);

ALTER TABLE `fornecedor` ADD PRIMARY KEY (`id`);

ALTER TABLE `cliente` ADD PRIMARY KEY (`id`);

ALTER TABLE `produto`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_produto_categoria` (`categoria_id`),
  ADD KEY `fk_produto_fornecedor` (`fornecedor_id`);

ALTER TABLE `variacao`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_variacao_produto` (`produto_id`);

ALTER TABLE `estoque`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `variacao_id` (`variacao_id`);

ALTER TABLE `movimento_estoque`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_mov_variacao` (`variacao_id`),
  ADD KEY `idx_mov_data` (`data`),
  ADD KEY `idx_mov_origem` (`origem`,`origem_id`),
  ADD KEY `idx_mov_tipo` (`tipo`);

ALTER TABLE `entrada_mercadoria`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_entrada_fornecedor` (`fornecedor_id`),
  ADD KEY `idx_entrada_data` (`data`),
  ADD KEY `idx_entrada_status` (`status`);

ALTER TABLE `entrada_item`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_entrada_item_entrada` (`entrada_id`),
  ADD KEY `idx_entrada_item_variacao` (`variacao_id`);

ALTER TABLE `pedido`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pedido_data` (`data_pedido`),
  ADD KEY `fk_pedido_produto` (`produto_id`);

ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

ALTER TABLE `venda`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_venda_usuario` (`usuario_id`),
  ADD KEY `idx_venda_cliente` (`cliente_id`),
  ADD KEY `idx_venda_data` (`data`),
  ADD KEY `idx_venda_status` (`status`);

ALTER TABLE `venda_item`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_venda_item_venda` (`venda_id`),
  ADD KEY `idx_venda_item_variacao` (`variacao_id`);

-- ============================================================
-- AUTO_INCREMENT
-- ============================================================
ALTER TABLE `categoria1` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
ALTER TABLE `fornecedor` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `cliente` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `produto` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;
ALTER TABLE `variacao` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;
ALTER TABLE `estoque` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `movimento_estoque` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `entrada_mercadoria` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `entrada_item` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `pedido` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `usuario` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
ALTER TABLE `venda` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `venda_item` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

-- ============================================================
-- Chaves estrangeiras
-- ============================================================
ALTER TABLE `produto`
  ADD CONSTRAINT `fk_produto_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `categoria1` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_produto_fornecedor` FOREIGN KEY (`fornecedor_id`) REFERENCES `fornecedor` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `variacao`
  ADD CONSTRAINT `fk_variacao_produto` FOREIGN KEY (`produto_id`) REFERENCES `produto` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `estoque`
  ADD CONSTRAINT `fk_estoque_variacao` FOREIGN KEY (`variacao_id`) REFERENCES `variacao` (`id`) ON UPDATE CASCADE;

ALTER TABLE `movimento_estoque`
  ADD CONSTRAINT `fk_movimento_variacao` FOREIGN KEY (`variacao_id`) REFERENCES `variacao` (`id`) ON UPDATE CASCADE;

ALTER TABLE `entrada_mercadoria`
  ADD CONSTRAINT `fk_entrada_fornecedor` FOREIGN KEY (`fornecedor_id`) REFERENCES `fornecedor` (`id`) ON UPDATE CASCADE;

ALTER TABLE `entrada_item`
  ADD CONSTRAINT `fk_entrada_item_entrada` FOREIGN KEY (`entrada_id`) REFERENCES `entrada_mercadoria` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_entrada_item_variacao` FOREIGN KEY (`variacao_id`) REFERENCES `variacao` (`id`) ON UPDATE CASCADE;

ALTER TABLE `pedido`
  ADD CONSTRAINT `fk_pedido_produto` FOREIGN KEY (`produto_id`) REFERENCES `produto` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `venda`
  ADD CONSTRAINT `fk_venda_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `cliente` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_venda_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`id`) ON UPDATE CASCADE;

ALTER TABLE `venda_item`
  ADD CONSTRAINT `fk_venda_item_variacao` FOREIGN KEY (`variacao_id`) REFERENCES `variacao` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_venda_item_venda` FOREIGN KEY (`venda_id`) REFERENCES `venda` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

COMMIT;
