-- ============================================================
-- Migração: dados da empresa (CNPJ + lugar) para a Nota Fiscal
-- ============================================================
-- Execute UMA VEZ no banco `projetorrgi51`.
--
-- Antes, o CNPJ/endereço que aparecem no cabeçalho da nota fiscal
-- eram fixos e inventados no próprio código. Agora ficam guardados
-- aqui: o gerente digita o CNPJ real do restaurante em
-- "?controller=empresa&action=index", o sistema busca os dados
-- públicos na Receita Federal (via BrasilAPI, direto do navegador)
-- e já preenche razão social e lugar sozinho — dá pra editar à mão
-- depois, se quiser.
--
-- Só existe UMA linha nesta tabela (id fixo = 1): é a configuração
-- do restaurante, não um cadastro de várias empresas.
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
