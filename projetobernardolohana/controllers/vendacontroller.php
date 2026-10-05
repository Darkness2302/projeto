<?php
require_once __DIR__ . '/../models/venda.php';
require_once __DIR__ . '/../models/produto.php';
require_once __DIR__ . '/../helpers/acl.php';

class VendaController
{
    // Tela de Pedidos: formulário de registro + histórico (Figma node 2:97)
    public function index(): void
    {
        acl_exigirPerfil(['garçom']);

        $pedidos     = [];
        $produtosSel = [];
        $erroBanco   = null;
        try {
            $pedidos     = (new Venda())->listar();
            $produtosSel = (new Produto())->listarComCategoria(true);
        } catch (PDOException $e) {
            // Em vez de tela branca: avisa que falta rodar a migração
            $erroBanco = 'Não foi possível carregar os pedidos. Confira se os scripts da pasta '
                       . '"Arquivos importantes" (migracao_produto_estoque.sql e migracao_pedidos.sql) '
                       . 'já foram executados no banco.';
        }

        // Mensagem de "Pedido registrado/removido" (aparece uma única vez após o redirecionamento)
        $flash = $_SESSION['flash_pedido'] ?? null;
        unset($_SESSION['flash_pedido']);

        require_once __DIR__ . '/../views/vendas.php';
    }

    // Botão "Adicionar": registra o pedido, GERA a nota fiscal sozinho
    // (não tem mais campo de nota fiscal pra digitar) e ele já aparece
    // no topo do histórico.
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

            // "Hora/Data" vem de <input type="datetime-local"> (ex.: 2026-09-28T14:30); vazio = agora
            $dataPedido      = $this->parseDataHora($_POST['data_pedido'] ?? '') ?? date('Y-m-d H:i:s');
            $horaRecebimento = $this->parseHora($_POST['hora_recebimento'] ?? '');

            $vendaModel = new Venda();
            $id = $vendaModel->inserir(
                $produtoId, $produto['nome'], $quantidade, $precoUnitario, $dataPedido, $horaRecebimento
            );
            $pedido = $vendaModel->buscarPorId($id);
        } catch (PDOException $e) {
            $this->voltar('erro', 'Não foi possível salvar o pedido. Confira se as migrações migracao_pedidos.sql e migracao_nota_fiscal.sql foram executadas.');
        }

        $numeroNota = $pedido['nota_fiscal'] ?? '';
        $this->voltar('ok', "Pedido #{$id} registrado — nota fiscal {$numeroNota} emitida.");
    }

    // Mostra a nota fiscal de um pedido (visualizar/imprimir)
    public function notaFiscal(): void
    {
        acl_exigirPerfil(['garçom']);

        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            die("ID inválido.");
        }

        $pedido = (new Venda())->buscarPorId($id);
        if (!$pedido) {
            die("Pedido não encontrado.");
        }

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
