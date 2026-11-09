<?php
require_once __DIR__ . '/../models/empresa.php';
require_once __DIR__ . '/../helpers/acl.php';

class EmpresaController
{
    // Tela de configuração: CNPJ + lugar que aparecem na Nota Fiscal
    public function index(): void
    {
        acl_exigirPerfil([]); // só gerente

        $empresa = (new Empresa())->buscar();
        $flash = $_SESSION['flash_empresa'] ?? null;
        unset($_SESSION['flash_empresa']);

        require_once __DIR__ . '/../views/empresa.php';
    }

    public function salvar(): void
    {
        acl_exigirPerfil([]); // só gerente

        $cnpj        = preg_replace('/\D/', '', $_POST['cnpj'] ?? '');
        $razaoSocial = trim($_POST['razao_social'] ?? '');
        $lugar       = trim($_POST['lugar'] ?? '');
        $lugar       = $lugar === '' ? null : $lugar;
        $telefone    = trim($_POST['telefone'] ?? '');
        $telefone    = $telefone === '' ? null : $telefone;

        if ($razaoSocial === '') {
            $this->voltar('erro', 'Informe a razão social.');
        }
        if (!Empresa::cnpjValido($cnpj)) {
            $this->voltar('erro', 'CNPJ inválido — confira os números digitados.');
        }

        try {
            (new Empresa())->salvar($cnpj, $razaoSocial, $lugar, $telefone);
        } catch (PDOException $e) {
            $this->voltar('erro', 'Não foi possível salvar. Confira se a migração migracao_empresa.sql foi executada.');
        }

        $this->voltar('ok', 'Dados da empresa salvos — já valem para as próximas notas fiscais.');
    }

    private function voltar(?string $tipo = null, ?string $msg = null): void
    {
        if ($tipo !== null && $msg !== null) {
            $_SESSION['flash_empresa'] = ['tipo' => $tipo, 'msg' => $msg];
        }
        header("Location: index.php?controller=empresa&action=index");
        exit;
    }
}
