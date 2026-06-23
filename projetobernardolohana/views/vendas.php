<?php
// $pedidos = array de pedidos (ainda a ser implementado no VendaController)
$pedidos = $pedidos ?? [];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Panela Quente – Pedidos</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;800&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    :root {
      --salmon:   #f3bfac;
      --green:    #a8c288;
      --green-dk: #505c41;
      --teal:     #1f99ac;
      --gray-img: #d9d9d9;
      --white:    #ffffff;
      --black:    #111111;
      --divider:  #c8c8c8;
    }
    body { font-family:'Inter',sans-serif; background:var(--white); color:var(--black); min-height:100vh; }

    /* HEADER */
    .site-header { display:flex; align-items:center; padding:20px 48px 16px 44px; }
    .header-logo { width:112px; height:auto; flex-shrink:0; }
    .header-right { flex:1; display:flex; flex-direction:column; align-items:center; gap:16px; }
    .header-title { font-size:1.85rem; font-weight:600; color:var(--black); text-align:center; }
    .nav-pills { display:flex; gap:16px; align-items:center; }
    .nav-pills a { display:inline-block; padding:7px 36px; background:linear-gradient(90deg, var(--green) 60%, var(--green-dk) 100%); color:var(--black); font-weight:600; font-size:1.2rem; text-decoration:none; border-radius:200px; transition:opacity .15s, transform .15s; }
    .nav-pills a:hover { opacity:.82; transform:translateY(-2px); }
    .nav-pills a.active { background:linear-gradient(90deg, var(--green-dk) 0%, #2d3620 100%); color:#fff; }
    .h-divider { height:1px; background:var(--divider); }

    /* ORDERS LIST */
    .orders-wrap { padding:28px 56px 64px 44px; display:flex; flex-direction:column; gap:0; }

    .order-item { display:flex; align-items:stretch; gap:26px; padding:14px 0; border-bottom:1px solid #f0ece7; }
    .order-item:last-child { border-bottom:none; }

    .order-img { width:200px; min-width:200px; height:160px; background:var(--gray-img); flex-shrink:0; overflow:hidden; }
    .order-img img { width:100%; height:100%; object-fit:cover; }

    .order-content { flex:1; display:flex; flex-direction:column; justify-content:space-between; gap:10px; padding:8px 0; }
    .order-badges { display:flex; gap:28px; align-items:center; }
    .badge-name { background:var(--salmon); border-radius:5px; height:56px; flex:0 0 320px; display:flex; align-items:center; padding:0 20px; font-size:1rem; font-weight:400; color:var(--black); }
    .badge-qty  { background:var(--salmon); border-radius:5px; height:56px; flex:0 0 300px; display:flex; align-items:center; padding:0 20px; font-size:1.1rem; font-weight:400; color:var(--black); }

    .order-timestamp { display:flex; flex-direction:column; gap:2px; line-height:1.3; }
    .ts-horario { font-size:1.2rem; font-weight:800; color:var(--teal); }
    .ts-data    { font-size:1.2rem; font-weight:800; color:var(--black); }

    /* EMPTY STATE */
    .empty-state { text-align:center; padding:64px 24px; color:#888; }
    .empty-state p { font-size:1.2rem; }

    @media (max-width:860px) {
      .site-header  { padding:16px 20px; }
      .orders-wrap  { padding:20px 16px 48px; }
      .header-title { font-size:1.3rem; }
      .nav-pills a  { font-size:.95rem; padding:6px 20px; }
      .order-img    { width:110px; min-width:110px; height:110px; }
      .badge-name   { flex:1; min-width:0; }
      .badge-qty    { flex:0 0 140px; }
      .order-badges { gap:12px; }
    }
    @media (max-width:540px) {
      .order-badges { flex-wrap:wrap; }
      .badge-qty    { flex:1; min-width:0; }
    }
  </style>
</head>
<body>

  <header class="site-header">
    <img class="header-logo"
      src="public/uploads/img/Cópia de R.P.Q. Logo (2) 4.png"
      alt="Restaurante Panela Quente" />
    <div class="header-right">
      <h1 class="header-title">Acesse o histórico de pedidos aqui:</h1>
      <nav class="nav-pills">
        <a href="/projetobernardolohana/index.php?controller=auth&action=dashboard">Menu</a>
        <a href="/projetobernardolohana/index.php?controller=produto&action=index">Estoque</a>
        <a href="/projetobernardolohana/index.php?controller=venda&action=index" class="active">Pedidos</a>
      </nav>
    </div>
  </header>

  <div class="h-divider"></div>

  <div class="orders-wrap">
    <?php if (empty($pedidos)): ?>
      <div class="empty-state">
        <p>Nenhum pedido registrado ainda.</p>
      </div>
    <?php else: ?>
      <?php foreach ($pedidos as $pedido): ?>
        <div class="order-item">
          <div class="order-img">
            <?php if (!empty($pedido['imagem'])): ?>
              <img src="<?= htmlspecialchars($pedido['imagem']) ?>" alt="Produto" />
            <?php endif; ?>
          </div>
          <div class="order-content">
            <div class="order-badges">
              <div class="badge-name"><?= htmlspecialchars($pedido['nome'] ?? '') ?></div>
              <div class="badge-qty">Qtd: <?= htmlspecialchars($pedido['quantidade'] ?? '') ?></div>
            </div>
            <div class="order-timestamp">
              <span class="ts-horario">
                <?= htmlspecialchars($pedido['hora_pedido'] ?? '') ?>
                <?php if (!empty($pedido['hora_recebimento'])): ?>
                  – <?= htmlspecialchars($pedido['hora_recebimento']) ?>
                <?php endif; ?>
              </span>
              <span class="ts-data"><?= htmlspecialchars($pedido['data'] ?? '') ?></span>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

</body>
</html>
