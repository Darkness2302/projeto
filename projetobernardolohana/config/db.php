<?php
//Informações do acesso
$host = "localhost";
$banco ="loja_roupas";
$usuario ="root";
$senha ="";
try {
    //Tentativa de conexão. Como o php e o MySQL se conectam
    $conexao = new PDO("mysql:host=$host;dbname=$banco", $usuario, $senha);
    //Se conectar dá isso
    echo "Conexão bem-sucedida!";
}
catch (PDOException $erro) {
    //Se não conectar dá isso. Por algum erro no código ou no BD ou em senha...
    echo "Ops, deu erro" . $erro->getMessage();
}
?>