<?php
//Inclui seu arquivo de conexão
require_once 'config/db.php';
try { 
    //Tenta obter a conexão
    $db = $conexao; //Usando a conexao do db.php
    //Executa o comando para executar a tarefa
    $query = $db->query("SHOW TABLES");
    $tabelasEncontradas = $query->fetchAll(PDO :: FETCH_COLUMN);
    $total = count($tabelasEncontradas);
    $meta = 14;
    echo "<h1> Verificação de Sistema </h1>";
    //Lógica de comparação
    if($total == $meta) {
        echo "<p style= 'color: green; '>Sucesso! O banco de dados está completo com as 14 tabelas.</p>";
    } else {
        $faltando - $meta - $total;
        echo "<p style='color: red;'>Atenção: O banco possui $total tabelas. Estão <strong> faltando $faltando</strong> para chegar ás 14.</p>";
    }
    //Listagem das tabelas existentes
    echo "<h3> Tabelas encontradas no banco: </h3>";
    echo "<ul>";
    foreach ($tabelasEncontradas as $tabela) {
        echo "<li>$tabela</li>";
    }
    echo "</ul>";
    } catch (Exception $e) {
        echo "Erro ao carregar o teste: " . $e->getMessage();
    }
?>