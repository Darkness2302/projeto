<?php
require_once __DIR__ . '/../models/venda.php';
require_once __DIR__ . '/../models/produto.php';
require_once __DIR__ . '/../helpers/acl.php';

class VendaController
{
    // Tela de Pedidos: formulário de registro + histórico (Figma node 2:97)
    public function index(): void
    {
        acl_exigirPerfil(['garcom']);

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

    // Botão "Adicionar": registra o pedido e ele já aparece no topo do histórico
    public function adicionar(): void
    {
        acl_exigirPerfil(['garcom']);
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            $this->voltar();
        }

        $produtoId  = (int)($_POST['produto_id'] ?? 0);
        $quantidade = (int)($_POST['quantidade'] ?? 0);
        $notaFiscal = trim($_POST['nota_fiscal'] ?? '');
        if ($notaFiscal !== '') {
            // mb_substr só existe com a extensão mbstring; sem ela usa substr (o campo já limita a 60 no HTML)
            $notaFiscal = function_exists('mb_substr') ? mb_substr($notaFiscal, 0, 60) : substr($notaFiscal, 0, 60);
        } else {
            $notaFiscal = null;
        }

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

            // "Hora/Data" vem de <input type="datetime-local"> (ex.: 2026-09-28T14:30); vazio = agora
            $dataPedido      = $this->parseDataHora($_POST['data_pedido'] ?? '') ?? date('Y-m-d H:i:s');
            $horaRecebimento = $this->parseHora($_POST['hora_recebimento'] ?? '');

            $id = (new Venda())->inserir(
                $produtoId, $produto['nome'], $quantidade, $dataPedido, $horaRecebimento, $notaFiscal
            );
        } catch (PDOException $e) {
            $this->voltar('erro', 'Não foi possível salvar o pedido. Confira se a migração migracao_pedidos.sql foi executada.');
        }

        $this->voltar('ok', "Pedido #{$id} registrado no histórico.");
    }

    // Botão "Remover": remove o pedido cujo número foi digitado em "ID Pedido"
    public function remover(): void
    {
        acl_exigirPerfil(['garcom']);

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
