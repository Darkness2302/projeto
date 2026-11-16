<?php
require_once __DIR__ . '/../models/venda.php';
require_once __DIR__ . '/../models/produto.php';
require_once __DIR__ . '/../models/produtovariacao.php';
require_once __DIR__ . '/../models/empresa.php';
require_once __DIR__ . '/../helpers/acl.php';

class VendaController
{
    /** Promoção: a partir desta quantidade de itens no pedido, 10% de desconto. */
    const DESCONTO_QTD_MIN = 7;
    const DESCONTO_PERCENTUAL = 10;

    // Tela de Pedidos: formulário de registro + histórico (Figma node 2:97)
    public function index(): void
    {
        acl_exigirPerfil(['garçom']);

        $pedidos     = [];
        $produtosSel = [];
        $variacoes   = [];
        $erroBanco   = null;
        try {
            $pedidos     = (new Venda())->listar();
            $produtosSel = (new Produto())->listarComCategoria(true);
            $variacoes   = (new ProdutoVariacao())->listarAtivas();
        } catch (PDOException $e) {
            // Em vez de tela branca: avisa que falta rodar a migração
            $erroBanco = 'Não foi possível carregar os pedidos. Confira se os scripts da pasta '
                       . '"Arquivos importantes" (migracao_produto_estoque.sql e migracao_pedidos.sql) '
                       . 'já foram executados no banco.';
        }

        $rascunho = $_SESSION['pedido_garcom'] ?? [];
        $rascTotal = 0.0; $rascQtd = 0;
        foreach ($rascunho as $r) {
            $rascQtd += (int)$r['quantidade'];
            $rascTotal += (int)$r['quantidade'] * (float)$r['preco_unitario'];
        }
        $rascDesconto = $rascQtd >= self::DESCONTO_QTD_MIN ? round($rascTotal * self::DESCONTO_PERCENTUAL / 100, 2) : 0.0;
        $rascFaltam = max(0, self::DESCONTO_QTD_MIN - $rascQtd);

        // Mensagem de "Pedido registrado/removido" (aparece uma única vez após o redirecionamento)
        $flash = $_SESSION['flash_pedido'] ?? null;
        unset($_SESSION['flash_pedido']);

        require_once __DIR__ . '/../views/vendas.php';
    }

    // Botão "Adicionar": coloca o produto NO PEDIDO EM MONTAGEM (sessão).
    // Nada vai ao banco até "Finalizar pedido", que gera UMA nota para todos os itens.
    public function adicionar(): void
    {
        acl_exigirPerfil(['garçom']);
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            $this->voltar();
        }

        $produtoId  = (int)($_POST['produto_id'] ?? 0);
        $quantidade = (int)($_POST['quantidade'] ?? 0);
        if ($produtoId <= 0) {
            $this->voltar('erro', 'Escolha o produto do pedido.');
        }
        if ($quantidade <= 0) {
            $this->voltar('erro', 'Informe uma quantidade maior que zero.');
        }

        try {
            $produto = (new Produto())->buscarPorId($produtoId);
            if (!$produto) {
                $this->voltar('erro', 'Produto não encontrado.');
            }
            $precoUnitario = (float)($produto['preco'] ?? 0);

            $variacaoTexto = null;
            $variacaoModel = new ProdutoVariacao();
            $variacaoId    = (int)($_POST['variacao_id'] ?? 0);
            if ($variacaoId > 0) {
                $opcao = $variacaoModel->buscarDoProduto($variacaoId, $produtoId);
                if (!$opcao) {
                    $this->voltar('erro', 'Variação inválida para este produto.');
                }
                $precoUnitario = (float)$opcao['preco'];
                $variacaoTexto = (!empty($produto['variacao']) ? $produto['variacao'] . ': ' : '') . $opcao['nome'];
            } elseif (!empty($variacaoModel->listarDoProduto($produtoId))) {
                $this->voltar('erro', 'Escolha a variação do produto.');
            }
        } catch (PDOException $e) {
            $this->voltar('erro', 'Não foi possível consultar o produto. Confira se as migrações foram executadas.');
        }

        // Estoque: considera o que já está no pedido em montagem
        $rascunho = $_SESSION['pedido_garcom'] ?? [];
        $noRascunho = 0;
        foreach ($rascunho as $r) {
            if ((int)$r['produto_id'] === $produtoId) $noRascunho += (int)$r['quantidade'];
        }
        $estoque = (int)$produto['estoque_qtd'];
        if ($estoque <= 0) {
            $this->voltar('erro', "{$produto['nome']} está esgotado no momento.");
        }
        if ($noRascunho + $quantidade > $estoque) {
            $restam = max(0, $estoque - $noRascunho);
            $this->voltar('erro', "Estoque insuficiente para {$produto['nome']}: restam {$restam} unidade(s).");
        }

        $rascunho[] = [
            'produto_id'     => $produtoId,
            'produto_nome'   => $produto['nome'],
            'variacao'       => $variacaoTexto,
            'quantidade'     => $quantidade,
            'preco_unitario' => $precoUnitario,
        ];
        $_SESSION['pedido_garcom'] = $rascunho;
        $this->voltar('ok', "{$produto['nome']} adicionado ao pedido. Clique em \"Finalizar pedido\" quando terminar.");
    }

    // Remove um item do pedido em montagem
    public function removerItem(): void
    {
        acl_exigirPerfil(['garçom']);
        $i = (int)($_GET['i'] ?? -1);
        if (isset($_SESSION['pedido_garcom'][$i])) {
            unset($_SESSION['pedido_garcom'][$i]);
            $_SESSION['pedido_garcom'] = array_values($_SESSION['pedido_garcom']);
        }
        $this->voltar('ok', 'Item removido do pedido.');
    }

    // Descarta o pedido em montagem (nada foi gravado)
    public function cancelarPedido(): void
    {
        acl_exigirPerfil(['garçom']);
        unset($_SESSION['pedido_garcom']);
        $this->voltar('ok', 'Pedido em montagem cancelado.');
    }

    // "Finalizar pedido": grava TODOS os itens com UMA nota fiscal e aplica o desconto de 10%
    // quando a quantidade total chega a 7 itens.
    public function finalizar(): void
    {
        acl_exigirPerfil(['garçom']);
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            $this->voltar();
        }
        $rascunho = $_SESSION['pedido_garcom'] ?? [];
        if (empty($rascunho)) {
            $this->voltar('erro', 'Adicione pelo menos um produto ao pedido antes de finalizar.');
        }

        $qtdItens = (int)array_sum(array_column($rascunho, 'quantidade'));
        $aplicaDesconto = $qtdItens >= self::DESCONTO_QTD_MIN;
        $linhas = array_map(fn($r) => [
            'produto_id'     => $r['produto_id'],
            'produto_nome'   => $r['produto_nome'],
            'variacao'       => $r['variacao'],
            'quantidade'     => $r['quantidade'],
            'preco_unitario' => $r['preco_unitario'],
            'desconto'       => $aplicaDesconto
                                ? round($r['preco_unitario'] * $r['quantidade'] * self::DESCONTO_PERCENTUAL / 100, 2)
                                : 0.0,
        ], $rascunho);

        $dataPedido      = $this->parseDataHora($_POST['data_pedido'] ?? '') ?? date('Y-m-d H:i:s');
        $horaRecebimento = $this->parseHora($_POST['hora_recebimento'] ?? '');

        try {
            $nota = (new Venda())->inserirCarrinho($linhas, (int)$_SESSION['usuario_id'], $dataPedido, $horaRecebimento);
        } catch (EstoqueInsuficienteException $e) {
            $this->voltar('erro', 'Pedido não efetuado: ' . $e->getMessage());
        } catch (PDOException $e) {
            $this->voltar('erro', 'Não foi possível salvar o pedido. Confira se as migrações foram executadas.');
        }

        unset($_SESSION['pedido_garcom']);
        $msg = "Pedido registrado — nota fiscal {$nota} emitida.";
        if ($aplicaDesconto) $msg .= ' Desconto de ' . self::DESCONTO_PERCENTUAL . '% aplicado.';
        $this->voltar('ok', $msg);
    }

    // Mostra a nota fiscal de um pedido (visualizar/imprimir)
    public function notaFiscal(): void
    {
        acl_exigirPerfil(['garçom', 'cliente']);

        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            die("ID inválido.");
        }

        $vendaModel = new Venda();
        $pedido = $vendaModel->buscarPorId($id);
        if (!$pedido) {
            die("Pedido não encontrado.");
        }

        // Cliente só enxerga a nota dos próprios pedidos
        if (($_SESSION['perfil'] ?? '') === 'cliente'
            && (int)($pedido['usuario_id'] ?? 0) !== (int)$_SESSION['usuario_id']) {
            http_response_code(403);
            require_once __DIR__ . '/../views/acesso_negado.php';
            exit;
        }

        // Um pedido do cliente pode ter vários itens sob a mesma nota
        $itens = !empty($pedido['nota_fiscal']) ? $vendaModel->buscarPorNota($pedido['nota_fiscal']) : [];
        if (empty($itens)) {
            $itens = [$pedido];
        }

        $empresa = (new Empresa())->buscar(); // null se o gerente ainda não configurou (ver ?controller=empresa)

        require_once __DIR__ . '/../views/nota_fiscal.php';
    }

    // Botão "Remover": remove o pedido cujo número foi digitado em "ID Pedido"
    public function remover(): void
    {
        acl_exigirPerfil(['garçom']);

        $id = (int)($_POST['id_pedido'] ?? 0);
        if ($id <= 0) {
            $this->voltar('erro', 'Digite o ID do pedido que deseja remover.');
        }

        try {
            $model = new Venda();
            if (!$model->buscarPorId($id)) {
                $this->voltar('erro', "Pedido #{$id} não encontrado.");
            }
            $model->deletar($id);
        } catch (PDOException $e) {
            $this->voltar('erro', 'Não foi possível remover o pedido.');
        }

        $this->voltar('ok', "Pedido #{$id} removido do histórico.");
    }

    // -------------------------
    // Auxiliares
    // -------------------------
    private function voltar(?string $tipo = null, ?string $msg = null): void
    {
        if ($tipo !== null && $msg !== null) {
            $_SESSION['flash_pedido'] = ['tipo' => $tipo, 'msg' => $msg];
        }
        header("Location: index.php?controller=venda&action=index");
        exit;
    }

    private function parseDataHora(string $v): ?string
    {
        $v = trim($v);
        if ($v === '') return null;
        foreach (['Y-m-d\TH:i', 'Y-m-d\TH:i:s', 'Y-m-d H:i:s', 'Y-m-d H:i'] as $fmt) {
            $dt = DateTime::createFromFormat($fmt, $v);
            if ($dt !== false) return $dt->format('Y-m-d H:i:s');
        }
        return null;
    }

    private function parseHora(string $v): ?string
    {
        $v = trim($v);
        if ($v === '') return null;
        foreach (['H:i', 'H:i:s'] as $fmt) {
            $dt = DateTime::createFromFormat($fmt, $v);
            if ($dt !== false) return $dt->format('H:i:s');
        }
        return null;
    }
}
