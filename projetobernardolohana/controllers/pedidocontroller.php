<?php
require_once __DIR__ . '/../models/produto.php';
require_once __DIR__ . '/../models/produtovariacao.php';
require_once __DIR__ . '/../models/venda.php';
require_once __DIR__ . '/../helpers/acl.php';

/**
 * Pedidos feitos pelo cliente no cardápio.
 * O carrinho fica na sessão e guarda só produto, variação e quantidade:
 * preços e nomes são sempre lidos do banco na hora de mostrar e de finalizar.
 */
class PedidoController
{
    /** Promoção: a partir desta quantidade de itens no pedido, o cliente ganha o desconto. */
    const DESCONTO_QTD_MIN = 7;
    const DESCONTO_PERCENTUAL = 10;

    public function adicionar(): void
    {
        acl_exigirPerfil(['cliente']);
        $produtoId  = (int)($_POST['produto_id'] ?? 0);
        $variacaoId = (int)($_POST['variacao_id'] ?? 0);
        $quantidade = max(1, min(20, (int)($_POST['quantidade'] ?? 1)));

        $produto = $produtoId > 0 ? (new Produto())->buscarPorId($produtoId) : null;
        if (!$produto || (int)$produto['ativo'] !== 1) {
            $this->voltarCardapio('erro', 'Este produto não está disponível no momento.');
        }
        // Quantidade já no carrinho (somando todas as variações do mesmo produto) + a nova
        $noCarrinho = 0;
        foreach (($_SESSION['carrinho'] ?? []) as $c) {
            if ((int)$c['produto_id'] === $produtoId) $noCarrinho += (int)$c['quantidade'];
        }
        $estoque = (int)$produto['estoque_qtd'];
        if ($estoque <= 0) {
            $this->voltarCardapio('erro', "{$produto['nome']} está esgotado no momento.");
        }
        if ($noCarrinho + $quantidade > $estoque) {
            $restam = max(0, $estoque - $noCarrinho);
            $this->voltarCardapio('erro', "Estoque insuficiente para {$produto['nome']}: restam {$restam} unidade(s).");
        }

        $variacoes = (new ProdutoVariacao())->listarDoProduto($produtoId);
        $temOpcoes = count(array_filter($variacoes, fn($v) => (int)$v['ativo'] === 1)) > 0;
        if ($temOpcoes) {
            if ($variacaoId <= 0) {
                $this->voltarCardapio('erro', 'Escolha a variação do produto.');
            }
            if (!(new ProdutoVariacao())->buscarDoProduto($variacaoId, $produtoId)) {
                $this->voltarCardapio('erro', 'Variação inválida para este produto.');
            }
        } else {
            $variacaoId = 0;
        }

        $carrinho = $_SESSION['carrinho'] ?? [];
        $chave = $produtoId . ':' . $variacaoId;
        $atual = (int)($carrinho[$chave]['quantidade'] ?? 0);
        $carrinho[$chave] = [
            'produto_id'  => $produtoId,
            'variacao_id' => $variacaoId ?: null,
            'quantidade'  => min(20, $atual + $quantidade),
        ];
        $_SESSION['carrinho'] = $carrinho;
        $this->voltarCardapio('ok', 'Adicionado ao seu pedido.');
    }

    public function carrinho(): void
    {
        acl_exigirPerfil(['cliente']);
        $itens = $this->itensDoCarrinho();
        $subtotal = (float)array_sum(array_column($itens, 'subtotal'));
        $qtdItens = (int)array_sum(array_column($itens, 'quantidade'));
        $temDesconto = $qtdItens >= self::DESCONTO_QTD_MIN;
        $desconto = $temDesconto ? round($subtotal * self::DESCONTO_PERCENTUAL / 100, 2) : 0.0;
        $total = $subtotal - $desconto;
        $faltam = max(0, self::DESCONTO_QTD_MIN - $qtdItens);
        $flash = $this->pegarFlash();
        require_once __DIR__ . '/../views/carrinho.php';
    }

    public function remover(): void
    {
        acl_exigirPerfil(['cliente']);
        $chave = (string)($_GET['chave'] ?? '');
        unset($_SESSION['carrinho'][$chave]);
        header("Location: index.php?controller=pedido&action=carrinho");
        exit;
    }

    public function finalizar(): void
    {
        acl_exigirPerfil(['cliente']);
        $itens = $this->itensDoCarrinho();
        if (empty($itens)) {
            $_SESSION['flash_cliente'] = ['tipo' => 'erro', 'msg' => 'Seu pedido está vazio.'];
            header("Location: index.php?controller=pedido&action=carrinho");
            exit;
        }

        // Desconto por quantidade total de itens; rateado por linha (cada linha guarda o seu)
        $qtdItens = (int)array_sum(array_column($itens, 'quantidade'));
        $aplicaDesconto = $qtdItens >= self::DESCONTO_QTD_MIN;

        $linhas = array_map(fn($i) => [
            'produto_id'     => $i['produto_id'],
            'produto_nome'   => $i['nome'],
            'variacao'       => $i['variacao_texto'],
            'quantidade'     => $i['quantidade'],
            'preco_unitario' => $i['preco'],
            'desconto'       => $aplicaDesconto ? round($i['subtotal'] * self::DESCONTO_PERCENTUAL / 100, 2) : 0.0,
        ], $itens);

        try {
            $nota = (new Venda())->inserirCarrinho($linhas, (int)$_SESSION['usuario_id'], date('Y-m-d H:i:s'));
        } catch (EstoqueInsuficienteException $e) {
            // o carrinho continua na sessão, para o cliente ajustar a quantidade
            $_SESSION['flash_cliente'] = ['tipo' => 'erro', 'msg' => 'Pedido não efetuado: ' . $e->getMessage()];
            header("Location: index.php?controller=pedido&action=carrinho");
            exit;
        } catch (PDOException $e) {
            $_SESSION['flash_cliente'] = ['tipo' => 'erro', 'msg' => 'Não foi possível confirmar o pedido. Tente novamente.'];
            header("Location: index.php?controller=pedido&action=carrinho");
            exit;
        }

        unset($_SESSION['carrinho']);
        $_SESSION['flash_cliente'] = ['tipo' => 'ok', 'msg' => "Pedido confirmado! Nota fiscal {$nota}."];
        header("Location: index.php?controller=pedido&action=meus");
        exit;
    }

    public function meus(): void
    {
        acl_exigirPerfil(['cliente']);
        $pedidos = (new Venda())->listarDoCliente((int)$_SESSION['usuario_id']);
        $flash = $this->pegarFlash();
        require_once __DIR__ . '/../views/meus_pedidos.php';
    }

    /** Itens do carrinho com preço e nome atuais. Remove da sessão o que ficou indisponível. */
    private function itensDoCarrinho(): array
    {
        $saida = [];
        foreach (($_SESSION['carrinho'] ?? []) as $chave => $c) {
            $produto = (new Produto())->buscarPorId((int)$c['produto_id']);
            if (!$produto || (int)$produto['ativo'] !== 1) {
                unset($_SESSION['carrinho'][$chave]);
                continue;
            }
            $preco = (float)$produto['preco'];
            $variacaoTexto = null;
            if (!empty($c['variacao_id'])) {
                $opcao = (new ProdutoVariacao())->buscarDoProduto((int)$c['variacao_id'], (int)$produto['id']);
                if (!$opcao) {
                    unset($_SESSION['carrinho'][$chave]);
                    continue;
                }
                $preco = (float)$opcao['preco'];
                $variacaoTexto = (!empty($produto['variacao']) ? $produto['variacao'] . ': ' : '') . $opcao['nome'];
            }
            $qtd = (int)$c['quantidade'];
            $saida[] = [
                'chave'          => $chave,
                'produto_id'     => (int)$produto['id'],
                'nome'           => $produto['nome'],
                'variacao_texto' => $variacaoTexto,
                'quantidade'     => $qtd,
                'preco'          => $preco,
                'subtotal'       => round($preco * $qtd, 2),
            ];
        }
        return $saida;
    }

    private function pegarFlash(): ?array
    {
        $f = $_SESSION['flash_cliente'] ?? null;
        unset($_SESSION['flash_cliente']);
        return $f;
    }

    private function voltarCardapio(string $tipo, string $msg): void
    {
        $_SESSION['flash_cliente'] = ['tipo' => $tipo, 'msg' => $msg];
        header("Location: index.php?controller=auth&action=dashboard");
        exit;
    }
}
