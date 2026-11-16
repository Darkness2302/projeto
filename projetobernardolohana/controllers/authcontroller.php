<?php
require_once __DIR__ . '/../models/usuario.php';
require_once __DIR__ . '/../models/produto.php';
require_once __DIR__ . '/../models/produtovariacao.php';
require_once __DIR__ . '/../models/categoria.php';
require_once __DIR__ . '/../helpers/acl.php';
class AuthController
{
public function form()
{
require_once __DIR__ . '/../views/login.php';
}
public function login()
{
$email = $_POST['email'] ?? '';
$senha = $_POST['senha'] ?? '';
$usuarioModel = new Usuario();
$user = $usuarioModel->buscarPorEmail($email);
if (!$user || (int)$user['ativo'] !== 1) {
die("Usuário inválido ou inativo.");
}
if (!password_verify($senha, $user['senha'])) {
die("Senha inválida.");
}
// sessão
$_SESSION['usuario_id'] = $user['id'];
$_SESSION['perfil'] = acl_normalizarPerfil((string)$user['perfil']);
$_SESSION['nome'] = $user['nome'];
header("Location: index.php?controller=auth&action=dashboard");
exit;
}
public function dashboard()
{
    acl_exigirPerfil(['cliente', 'garçom']);
    // Gerente e garçom gerenciam o cardápio: veem também os produtos ocultos e
    // categorias sem produtos (para poder adicionar). Cliente vê só o ativo.
    $admin = in_array($_SESSION['perfil'] ?? '', ['gerente', 'garçom'], true);

    $categoriaModel   = new Categoria1();
    $produtoModel     = new Produto();
    $categoriasAtivas = $categoriaModel->listarAtivas();
    $todosProdutos    = $produtoModel->listarComCategoria(!$admin);

    $categoriasProdutos = [];
    foreach ($categoriasAtivas as $cat) {
        $categoriasProdutos[$cat['id']] = ['nome' => $cat['nome'], 'produtos' => []];
    }
    foreach ($todosProdutos as $p) {
        if (isset($categoriasProdutos[$p['categoria_id']])) {
            $categoriasProdutos[$p['categoria_id']]['produtos'][] = $p;
        }
    }
    if (!$admin) {
        // cliente: não faz sentido mostrar carrossel vazio
        $categoriasProdutos = array_filter($categoriasProdutos, fn($c) => count($c['produtos']) > 0);
    }

    // Opções de variação por produto (para o cliente escolher ao pedir)
    $variacoesPorProduto = [];
    foreach ((new ProdutoVariacao())->listarAtivas() as $v) {
        $variacoesPorProduto[(int)$v['produto_id']][] = $v;
    }
    $carrinhoQtd = array_sum(array_column($_SESSION['carrinho'] ?? [], 'quantidade'));
    $flashCliente = $_SESSION['flash_cliente'] ?? null;
    unset($_SESSION['flash_cliente']);

    require_once __DIR__ . '/../views/dashboard.php';
}

public function logout()
{
session_destroy();
header("Location: index.php?controller=auth&action=form");
exit;
}
public function check()
{
if (!isset($_SESSION['usuario_id'])) {
header("Location: index.php?controller=auth&action=form");
exit;
} }
public function onlyAdmin()
{
$this->check();
if (($_SESSION['perfil'] ?? '') !== 'gerente') {
die("Acesso negado.");
} } }
