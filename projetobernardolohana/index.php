<?php
session_start();
require_once __DIR__ . '/config/db.php';
Database::getConnection();


// Roteamento simples via GET
$controller = $_GET['controller'] ?? 'auth';
$action = $_GET['action'] ?? 'form';


// Carregar controller
switch ($controller) {
    case 'auth':
        require_once __DIR__ . '/controllers/authcontroller.php';
        $c = new AuthController();
        break;


    // CRUD PRODUTOS E VENDAS
    case 'produto':
        require_once __DIR__ . '/controllers/produtocontroller.php';
        $c = new ProdutoController();
        break;


    case 'venda':
        require_once __DIR__ . '/controllers/vendacontroller.php';
        $c = new VendaController();
        break;


    case 'usuario':
        require_once __DIR__ . '/controllers/usuariocontroller.php';
        $c = new UsuarioController();
        break;
     case 'categoria':
         require_once __DIR__ . '/controllers/CategoriaController.php';
         $c = new CategoriaController();
        break;

    case 'fornecedor':
        require_once __DIR__ . '/controllers/fornecedorcontroller.php';
        $c = new FornecedorController();
        break;

    case 'funcionario':
        require_once __DIR__ . '/controllers/funcionariocontroller.php';
        $c = new FuncionarioController();
        break;

    case 'pedido':
        require_once __DIR__ . '/controllers/pedidocontroller.php';
        $c = new PedidoController();
        break;

    case 'empresa':
        require_once __DIR__ . '/controllers/empresacontroller.php';
        $c = new EmpresaController();
        break;


    // Caminho padrão caso o controller não exista
    default:
        die("Controller inválido.");
}


// Executar ação
if (!method_exists($c, $action)) {
    die("Ação inválida.");
}


$c->$action();

