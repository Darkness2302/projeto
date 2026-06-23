<?php
class VendaController
{
    public function index(): void
    {
        $this->check();
        // $pedidos virá do model de Venda quando implementado.
        // Por ora, passa array vazio para a view não quebrar.
        $pedidos = [];
        require_once __DIR__ . '/../views/vendas.php';
    }

    private function check(): void
    {
        if (!isset($_SESSION['usuario_id'])) {
            header("Location: index.php?controller=auth&action=form");
            exit;
        }
    }
}
