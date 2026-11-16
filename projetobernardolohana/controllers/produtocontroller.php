<?php
require_once __DIR__ . '/../models/produto.php';
require_once __DIR__ . '/../models/categoria.php';
require_once __DIR__ . '/../models/fornecedor.php';
require_once __DIR__ . '/../models/produtovariacao.php';
require_once __DIR__ . '/../models/entrada.php';
require_once __DIR__ . '/../helpers/acl.php';

class ProdutoController
{
    // Tela de Estoque: só listagem (Figma node 2:95)
    public function index(): void
    {
        acl_exigirPerfil(['garçom']);
        $produtoModel = new Produto();
        $produtos = $produtoModel->listarComCategoria(false);
        $fornecedores = (new Fornecedor())->listarAtivos();
        $flashEntrada = $_SESSION['flash_entrada'] ?? null;
        unset($_SESSION['flash_entrada']);
        require_once __DIR__ . '/../views/produtos.php';
    }

    // Tela de movimentações: entradas e vendas, com quem enviou/comprou
    public function movimentos(): void
    {
        acl_exigirPerfil(['garçom']);
        $tipo = in_array($_GET['tipo'] ?? '', ['entrada', 'venda'], true) ? $_GET['tipo'] : null;
        $modelo = new Entrada();
        $movimentos = $modelo->listarMovimentos($tipo);
        $resumo = $modelo->resumoPorProduto();
        $produtos = (new Produto())->listarComCategoria(false);
        $fornecedores = (new Fornecedor())->listarAtivos();
        $flashEntrada = $_SESSION['flash_entrada'] ?? null;
        unset($_SESSION['flash_entrada']);
        require_once __DIR__ . '/../views/movimentos.php';
    }

    // Registra a entrada de mercadoria vinda do fornecedor (soma no estoque e no histórico)
    public function entradaSalvar(): void
    {
        acl_exigirPerfil(['garçom']);
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            header('Location: index.php?controller=produto&action=movimentos');
            exit;
        }

        $produtoId   = (int)($_POST['produto_id'] ?? 0);
        $fornecedor  = (int)($_POST['fornecedor_id'] ?? 0);
        $quantidade  = (int)($_POST['quantidade'] ?? 0);
        $data        = $_POST['data'] ?? date('Y-m-d');

        if ($produtoId <= 0 || $quantidade <= 0) {
            $_SESSION['flash_entrada'] = ['tipo' => 'erro', 'msg' => 'Escolha o produto e informe uma quantidade maior que zero.'];
        } else {
            try {
                (new Entrada())->registrarEntrada(
                    $produtoId, $fornecedor > 0 ? $fornecedor : null, $quantidade,
                    $data, (int)$_SESSION['usuario_id']
                );
                $_SESSION['flash_entrada'] = ['tipo' => 'ok', 'msg' => "Entrada de {$quantidade} unidade(s) registrada no estoque."];
            } catch (Throwable $e) {
                $_SESSION['flash_entrada'] = ['tipo' => 'erro', 'msg' => 'Não foi possível registrar a entrada: ' . $e->getMessage()];
            }
        }

        header('Location: index.php?controller=produto&action=movimentos');
        exit;
    }

    // Página dedicada de Adição/Edição de produto (Figma node 394:308)
    // Sem ?id= -> formulário em branco (novo produto)
    // Com ?id= -> formulário pré-preenchido (edição)
    public function form(): void
    {
        acl_exigirPerfil(['garçom']);

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

        $variacoes = $editar ? (new ProdutoVariacao())->listarDoProduto((int)$editar['id']) : [];

        require_once __DIR__ . '/../views/produto_form.php';
    }

    public function salvar(): void
    {
        acl_exigirPerfil(['garçom']);

        $id          = (int)($_POST['id'] ?? 0);
        $categoriaId = (int)($_POST['categoria_id'] ?? 0);
        $nome        = trim($_POST['nome'] ?? '');
        $descricao   = trim($_POST['descricao'] ?? '');
        $descricao   = $descricao === '' ? null : $descricao;
        $variacao    = trim($_POST['variacao'] ?? '');
        $variacao    = $variacao === '' ? null : $variacao;

        $preco = $this->parsePreco($_POST['preco'] ?? '0');

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

        header("Location: " . $this->destinoAposAcao($_POST['voltar'] ?? ''));
        exit;
    }

    public function toggle(): void
    {
        acl_exigirPerfil(['garçom']);
        $id = (int)($_GET['id'] ?? 0);
        $ativo = (int)($_GET['ativo'] ?? 1);
        if ($id <= 0) die("ID inválido.");
        $produtoModel = new Produto();
        $produtoModel->setAtivo($id, $ativo === 1);
        header("Location: " . $this->destinoAposAcao($_GET['voltar'] ?? ''));
        exit;
    }

    public function deletar(): void
    {
        acl_exigirPerfil(['garçom']);
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

    /**
     * Aceita "28,50", "1.234,50", "R$ 12,5" e "12.50".
     * (Antes só trocava a vírgula por ponto, então "1.234,50" virava 1.234.)
     */
    // Opções de variação (ex.: P/M/G) de um produto, com preço próprio
    public function variacaoSalvar(): void
    {
        acl_exigirPerfil(['garçom']);
        $produtoId = (int)($_POST['produto_id'] ?? 0);
        $nome      = trim($_POST['nome_variacao'] ?? '');
        $preco     = $this->parsePreco($_POST['preco_variacao'] ?? '0');

        if ($produtoId <= 0 || !(new Produto())->buscarPorId($produtoId)) die("Produto não encontrado.");
        if ($nome === '') die("Informe o nome da opção (ex.: M).");

        (new ProdutoVariacao())->inserir($produtoId, $nome, $preco);
        $voltar = ($_POST['voltar'] ?? '') === 'cardapio' ? '&voltar=cardapio' : '';
        header("Location: index.php?controller=produto&action=form&id={$produtoId}{$voltar}");
        exit;
    }

    public function variacaoDeletar(): void
    {
        acl_exigirPerfil(['garçom']);
        $id        = (int)($_GET['id'] ?? 0);
        $produtoId = (int)($_GET['produto_id'] ?? 0);
        if ($id <= 0 || $produtoId <= 0) die("ID inválido.");

        (new ProdutoVariacao())->deletar($id, $produtoId);
        header("Location: index.php?controller=produto&action=form&id={$produtoId}");
        exit;
    }

    /** Volta para o cardápio se a ação veio de lá (voltar=cardapio); senão, para o Estoque. */
    private function destinoAposAcao(string $voltar): string
    {
        return $voltar === 'cardapio'
            ? 'index.php?controller=auth&action=dashboard'
            : 'index.php?controller=produto&action=index';
    }

    private function parsePreco(string $raw): float
    {
        $v = str_replace(['R$', ' '], '', trim($raw));
        if (strpos($v, ',') !== false) {
            // formato brasileiro: o ponto é separador de milhar e a vírgula é o decimal
            $v = str_replace('.', '', $v);
            $v = str_replace(',', '.', $v);
        } elseif (preg_match('/^\d{1,3}(\.\d{3})+$/', $v)) {
            // "1.234" ou "12.500" sem vírgula: milhar
            $v = str_replace('.', '', $v);
        }
        return max(0, (float)$v);
    }
}
