<?php
require_once __DIR__ . '/../models/produto.php';
require_once __DIR__ . '/../models/categoria.php';
require_once __DIR__ . '/../models/fornecedor.php';

class ProdutoController
{
    // Tela de Estoque: só listagem (Figma node 2:95)
    public function index(): void
    {
        $this->check();
        $produtoModel = new Produto();
        $produtos = $produtoModel->listarComCategoria(false);
        require_once __DIR__ . '/../views/produtos.php';
    }

    // Página dedicada de Adição/Edição de produto (Figma node 394:308)
    // Sem ?id= -> formulário em branco (novo produto)
    // Com ?id= -> formulário pré-preenchido (edição)
    public function form(): void
    {
        $this->check();
        $this->onlyAdmin();

        $categoriaModel  = new Categoria1();
        $fornecedorModel = new Fornecedor();
        $categorias   = $categoriaModel->listarAtivas();
        $fornecedores = $fornecedorModel->listarAtivos();

        $editar = null;
        if (isset($_GET['id'])) {
            $produtoModel = new Produto();
            $editar = $produtoModel->buscarPorId((int)$_GET['id']);
            if (!$editar) {
                die("Produto não encontrado.");
            }
        }

        require_once __DIR__ . '/../views/produto_form.php';
    }

    public function salvar(): void
    {
        $this->check();
        $this->onlyAdmin();

        $id          = (int)($_POST['id'] ?? 0);
        $categoriaId = (int)($_POST['categoria_id'] ?? 0);
        $nome        = trim($_POST['nome'] ?? '');
        $descricao   = trim($_POST['descricao'] ?? '');
        $descricao   = $descricao === '' ? null : $descricao;
        $variacao    = trim($_POST['variacao'] ?? '');
        $variacao    = $variacao === '' ? null : $variacao;

        $precoRaw = str_replace(['R$', ' '], '', $_POST['preco'] ?? '0');
        $precoRaw = str_replace(',', '.', $precoRaw);
        $preco    = max(0, (float)$precoRaw);

        $estoqueQtd = max(0, (int)($_POST['estoque_qtd'] ?? 0));

        $fornecedorId = (int)($_POST['fornecedor_id'] ?? 0);
        $fornecedorId = $fornecedorId > 0 ? $fornecedorId : null;

        if ($categoriaId <= 0 || $nome === '') {
            die("Dados inválidos.");
        }

        $produtoModel = new Produto();
        if ($id > 0) {
            $produtoModel->atualizar($id, $categoriaId, $nome, $descricao, $variacao, $preco, $estoqueQtd, $fornecedorId);
            $this->salvarImagemDoProduto($id);
        } else {
            $id = $produtoModel->inserir($categoriaId, $nome, $descricao, $variacao, $preco, $estoqueQtd, $fornecedorId);
            $this->salvarImagemDoProduto($id);
        }

        header("Location: index.php?controller=produto&action=index");
        exit;
    }

    public function toggle(): void
    {
        $this->check();
        $this->onlyAdmin();
        $id = (int)($_GET['id'] ?? 0);
        $ativo = (int)($_GET['ativo'] ?? 1);
        if ($id <= 0) die("ID inválido.");
        $produtoModel = new Produto();
        $produtoModel->setAtivo($id, $ativo === 1);
        header("Location: index.php?controller=produto&action=index");
        exit;
    }

    public function deletar(): void
    {
        $this->check();
        $this->onlyAdmin();
        $id = (int)($_GET['id'] ?? 0);

        if ($id <= 0) die("ID inválido.");

        $produtoModel = new Produto();
        $produto = $produtoModel->buscarPorId($id);

        if (!$produto) die("Produto não encontrado.");

        $this->deletarImagemDoProduto($id);
        $produtoModel->deletar($id);

        header("Location: index.php?controller=produto&action=index");
        exit;
    }

    // -------------------------
    // Upload (POO + seguro)
    // -------------------------
    private function salvarImagemDoProduto(int $produtoId): void
    {
        if (!isset($_FILES['imagem']) || $_FILES['imagem']['error'] !== UPLOAD_ERR_OK) {
            return; // sem imagem
        }
        // limita tamanho (2MB)
        if (($_FILES['imagem']['size'] ?? 0) > 2 * 1024 * 1024) {
            return; // em produção: mostrar mensagem
        }
        $tmp = $_FILES['imagem']['tmp_name'];
        $mime = mime_content_type($tmp);
        $ext = match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => null
        };
        if ($ext === null) return;
        $destDir = __DIR__ . '/../public/uploads/produtos/';
        if (!is_dir($destDir)) {
            mkdir($destDir, 0777, true);
        }
        // remove versões antigas (se trocar)
        foreach (['jpg', 'png', 'webp'] as $e) {
            $old = $destDir . $produtoId . '.' . $e;
            if (file_exists($old)) unlink($old);
        }
        $dest = $destDir . $produtoId . '.' . $ext;
        move_uploaded_file($tmp, $dest);
    }

    private function deletarImagemDoProduto(int $produtoId): void
    {
        $destDir = __DIR__ . '/../public/uploads/produtos/';
        foreach (['jpg', 'png', 'webp'] as $ext) {
            $arquivo = $destDir . $produtoId . '.' . $ext;
            if (file_exists($arquivo)) {
                unlink($arquivo);
            }
        }
    }

    private function check(): void
    {
        if (!isset($_SESSION['usuario_id'])) {
            header("Location: index.php?controller=auth&action=form");
            exit;
        }
    }

    private function onlyAdmin(): void
    {
        if (($_SESSION['perfil'] ?? '') !== 'gerente') {
            die("Acesso negado.");
        }
    }
}
