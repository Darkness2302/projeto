<?php
$nome = $_SESSION['nome'] ?? 'Usuário';
$perfil = $_SESSION['perfil'] ?? 'vendedor';
?>
<!doctype html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<title>Dashboard - Restaurante Panela Quente</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="public/assets/css/style.css">
</head>
<body>


<div class="container">
<div class="topbar">
<div class="brand">
<div class="badge"></div>
<div>
<h1>Painel do Sistema</h1>

</div>
</div>
<div class="pill">
Logado como <strong><?php echo htmlspecialchars($nome); ?></strong>
(<?php echo htmlspecialchars($perfil); ?>)
• <a href="/projetobernardolohana/index.php?controller=auth&action=logout">Sair</a>
</div>
</div>
<div class="card">
<h2 style="margin-top:0;">Bem-vindo(a), <?php echo htmlspecialchars($nome); ?>!</h2>
<p style="color:var(--muted); margin-top:6px;">
Escolha um módulo para continuar.
</p>
<div class="nav">


<a href="/projetobernardolohana/index.php?controller=produto&action=index">Produtos /


Categorias</a>
<a href="/projetobernardolohana/index.php?controller=entrada&action=index">Entradas</a>
<a href="/projetobernardolohana/index.php?controller=venda&action=index">Vendas</a>
<a href="/projetobernardolohana/index.php?controller=relatorio&action=index">Relatórios</a>
</div>
<div class="kpis">
<div class="kpi">
<div class="label">Vendas (mês)</div>
<div class="value">0</div>
</div>
<div class="kpi">
<div class="label">Entradas (mês)</div>
<div class="value">0</div>
</div>
<div class="kpi">
<div class="label">Estoque baixo</div>
<div class="value">0</div>
</div>




<div class="kpi">
<div class="label">Produtos</div>
<div class="value">0</div>
</div>
</div>
<p style="margin-top:16px; color:var(--muted); font-size:13px;">
Observação: esses números serão alimentados quando implementarmos os módulos de
entradas, vendas e estoque.
</p>
</div>
</div>
</body>
</html>
