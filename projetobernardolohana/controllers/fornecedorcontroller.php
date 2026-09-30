<?php
require_once __DIR__ . '/../models/fornecedor.php';
require_once __DIR__ . '/../helpers/acl.php';

class FornecedorController
{
    // Tela de Fornecedores: lista + formulário de adicionar/editar na mesma página
    public function index(): void
    {
        acl_exigirPerfil([]);
        $fornecedorModel = new Fornecedor();
        $fornecedores = $fornecedorModel->listarTodos();

        $editar = null;
        if (isset($_GET['id'])) {
            $editar = $fornecedorModel->buscarPorId((int)$_GET['id']);
        }

        require_once __DIR__ . '/../views/fornecedores.php';
    }

    public function salvar(): void
    {
        acl_exigirPerfil([]);

        $id       = (int)($_POST['id'] ?? 0);
        $nome     = trim($_POST['nome'] ?? '');
        $cpf      = trim($_POST['cpf'] ?? '');
        $cpf      = $cpf === '' ? null : $cpf;
        $lugar    = trim($_POST['lugar'] ?? '');
        $lugar    = $lugar === '' ? null : $lugar;
        $produtos = trim($_POST['produtos'] ?? '');
        $produtos = $produtos === '' ? null : $produtos;

        if ($nome === '') {
            die("Nome do fornecedor é obrigatório.");
        }

        // Registra automaticamente a data/hora deste recebimento/atualização,
        // exibida no card como "Hora de chegada dos produtos - Hora do recebimento (DATA)"
        $dataRecebimento = date('Y-m-d');
        $horaRecebimento = date('H:i:s');

        $fornecedorModel = new Fornecedor();
        if ($id > 0) {
            $fornecedorModel->atualizar($id, $nome, $cpf, $lugar, $produtos, $dataRecebimento, $horaRecebimento);
            $this->salvarLogoDoFornecedor($id);
        } else {
            $id = $fornecedorModel->inserir($nome, $cpf, $lugar, $produtos, $dataRecebimento, $horaRecebimento);
            $this->salvarLogoDoFornecedor($id);
        }

        header("Location: index.php?controller=fornecedor&action=index");
        exit;
    }

    public function toggle(): void
    {
        acl_exigirPerfil([]);
        $id = (int)($_GET['id'] ?? 0);
        $ativo = (int)($_GET['ativo'] ?? 1);
        if ($id <= 0) die("ID inválido.");
        $fornecedorModel = new Fornecedor();
        $fornecedorModel->setAtivo($id, $ativo === 1);
        header("Location: index.php?controller=fornecedor&action=index");
        exit;
    }

    public function deletar(): void
    {
        acl_exigirPerfil([]);
        $id = (int)($_GET['id'] ?? 0);

        if ($id <= 0) die("ID inválido.");

        $fornecedorModel = new Fornecedor();
        $fornecedor = $fornecedorModel->buscarPorId($id);

        if (!$fornecedor) die("Fornecedor não encontrado.");

        $this->deletarLogoDoFornecedor($id);
        $fornecedorModel->deletar($id);

        header("Location: index.php?controller=fornecedor&action=index");
        exit;
    }

    // -------------------------
    // Upload da logo do fornecedor (mesmo padrão do upload de produto)
    // -------------------------
    private function salvarLogoDoFornecedor(int $fornecedorId): void
    {
        if (!isset($_FILES['logo']) || $_FILES['logo']['error'] !== UPLOAD_ERR_OK) {
            return; // sem logo enviada
        }
        if (($_FILES['logo']['size'] ?? 0) > 2 * 1024 * 1024) {
            return; // em produção: mostrar mensagem
        }
        $tmp = $_FILES['logo']['tmp_name'];
        $mime = mime_content_type($tmp);
        $ext = match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => null
        };
        if ($ext === null) return;
        $destDir = __DIR__ . '/../public/uploads/fornecedores/';
        if (!is_dir($destDir)) {
            mkdir($destDir, 0777, true);
        }
        foreach (['jpg', 'png', 'webp'] as $e) {
            $old = $destDir . $fornecedorId . '.' . $e;
            if (file_exists($old)) unlink($old);
        }
        $dest = $destDir . $fornecedorId . '.' . $ext;
        move_uploaded_file($tmp, $dest);
    }

    private function deletarLogoDoFornecedor(int $fornecedorId): void
    {
        $destDir = __DIR__ . '/../public/uploads/fornecedores/';
        foreach (['jpg', 'png', 'webp'] as $ext) {
            $arquivo = $destDir . $fornecedorId . '.' . $ext;
            if (file_exists($arquivo)) {
                unlink($arquivo);
            }
        }
    }
}
