-- ============================================================
-- Migração: Funcionários (garçons) + Cargos
-- ============================================================
-- Execute UMA VEZ no banco `projetorrgi51`.
-- Funciona tanto em banco novo quanto em banco que já rodou a
-- versão anterior desta migração (as colunas antigas são removidas).
--
-- Telas: ?controller=funcionario&action=index (só gerente)
-- ============================================================

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
