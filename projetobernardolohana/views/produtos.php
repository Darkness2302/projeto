<?php
function imagemProdutoUrl(int $produtoId): string
{
    $baseFs  = __DIR__ . "/../public/uploads/produtos/";
    $baseUrl = "public/uploads/produtos/";
    foreach (['jpg', 'png', 'webp'] as $ext) {
        if (file_exists($baseFs . $produtoId . '.' . $ext)) {
            return $baseUrl . $produtoId . '.' . $ext;
        }
    }
    return "public/assets/img/produto_sem_foto.png";
}

function precoFormatado(?float $preco): string
{
    $preco = $preco ?? 0;
    return 'R$ ' . number_format($preco, 2, ',', '.');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Panela Quente – Estoque</title>
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
      --gray-ph:  #676767;
      --white:    #ffffff;
      --black:    #111111;
      --divider:  #c8c8c8;
      --red:      #fc2c2f;
      --orange:   #fd9c4d;
      --green-ok: #d4edda;
    }
    body { font-family:'Inter',sans-serif; background:var(--white); color:var(--black); min-height:100vh; }

    /* HEADER */
    .site-header { display:flex; align-items:center; padding:20px 48px 16px 40px; gap:24px; }
    .header-logo { width:112px; height:auto; flex-shrink:0; }
    .header-right { flex:1; display:flex; flex-direction:column; align-items:center; gap:14px; }
    .header-title { font-size:1.85rem; font-weight:500; color:var(--black); text-align:center; }
    .nav-pills { display:flex; gap:16px; align-items:center; flex-wrap:wrap; justify-content:center; }
    .nav-pills a { display:inline-block; padding:7px 36px; background:linear-gradient(90deg, var(--green) 60%, var(--green-dk) 100%); color:var(--black); font-weight:600; font-size:1.2rem; text-decoration:none; border-radius:200px; transition:opacity .15s, transform .15s; }
    .nav-pills a:hover { opacity:.82; transform:translateY(-2px); }
    .nav-pills a.active { background:linear-gradient(90deg, var(--green-dk) 0%, #2d3620 100%); color:#fff; }
    .h-divider { height:1px; background:var(--divider); }

    /* TOOLBAR (não faz parte do Figma original — adicionado para permitir cadastrar novo produto) */
    .toolbar { display:flex; justify-content:flex-end; padding:22px 56px 0; }
    .btn-novo {
      display:inline-flex; align-items:center; gap:8px; padding:12px 28px;
      background:linear-gradient(90deg, var(--green) 60%, var(--green-dk) 100%);
      border-radius:200px; font-weight:600; font-size:1rem; color:#111; text-decoration:none;
      box-shadow:0 3px 6px rgba(0,0,0,.18); transition:opacity .15s, transform .12s;
    }
    .btn-novo:hover { opacity:.88; transform:translateY(-1px); }

    /* TABLE HEADER */
    .table-wrap { padding:20px 56px 64px; }
    .table-head {
      display:grid;
      grid-template-columns: 230px 1fr 1fr 220px;
      gap:16px;
      margin-bottom: 22px;
    }
    .th { background:var(--salmon); border:1px solid #000; border-radius:5px; padding:14px 18px; font-size:1.15rem; font-weight:400; color:var(--black); display:flex; align-items:center; }
    .th-detalhes { grid-column: 3 / 5; justify-content:center; }

    /* PRODUCT CARD ROW */
    .produto-row {
      display:grid;
      grid-template-columns: 230px 1fr 1fr 220px;
      grid-template-rows: auto auto auto;
      gap:12px 16px;
      margin-bottom: 30px;
      align-items:stretch;
    }

    .cell-img {
      grid-column: 1;
      grid-row: 1 / 3;
      background:var(--gray-img); border:1px solid #000;
      display:flex; align-items:center; justify-content:center; overflow:hidden;
      min-height: 194px;
    }
    .cell-img img { width:100%; height:100%; object-fit:cover; }

    .badge {
      background:var(--salmon); border:1px solid #000; border-radius:5px;
      padding:14px 18px; font-size:1.1rem; font-weight:400; color:var(--black);
      display:flex; align-items:center; min-height:56px;
    }

    .badge-nome     { grid-column: 2; grid-row: 1; font-weight:600; }
    .badge-estoque  { grid-column: 2; grid-row: 2; }
    .badge-status   { grid-column: 3; grid-row: 1; }
    .badge-preco    { grid-column: 3; grid-row: 2; }

    .badge-status.ativo   { background:var(--green-ok); color:#155724; }
    .badge-status.inativo { background:#f8d7da; color:#721c24; }

    .btn-acao {
      border:1px solid #000; border-radius:5px; padding:14px 18px;
      font-size:1.1rem; font-weight:400; color:var(--black); text-align:center;
      text-decoration:none; cursor:pointer; display:flex; align-items:center; justify-content:center;
      min-height:56px; transition:opacity .15s;
    }
    .btn-acao:hover { opacity:.82; }

    .btn-inativar { grid-column: 4; grid-row: 1; background:var(--orange); }
    .btn-ativar   { grid-column: 4; grid-row: 1; background:var(--green-ok); color:#155724; }
    .btn-excluir  { grid-column: 4; grid-row: 2; background:var(--red); }

    .link-editar {
      grid-column: 2;
      grid-row: 3;
      font-size:1.2rem; font-weight:800; color:var(--teal); text-decoration:none;
      transition:opacity .15s;
    }
    .link-editar:hover { opacity:.7; text-decoration:underline; }

    .empty-msg { padding:40px; text-align:center; color:#777; font-size:1.1rem; }

    @media (max-width:900px) {
      .site-header { padding:16px 20px; flex-direction:column; }
      .table-wrap  { padding:20px 16px 48px; }
      .toolbar     { padding:16px 16px 0; }
      .header-title { font-size:1.3rem; }
      .nav-pills a  { font-size:.95rem; padding:6px 20px; }
      .table-head  { grid-template-columns: 90px 1fr 1fr 1fr; }
      .th          { font-size:.85rem; padding:8px; }
      .produto-row { grid-template-columns: 90px 1fr 1fr 1fr; }
      .cell-img    { min-height: 110px; }
      .badge, .btn-acao { font-size:.85rem; padding:8px; min-height:44px; }
      .link-editar { font-size:.95rem; }
    }
  </style>
</head>
<body>

  <header class="site-header">
    <img class="header-logo"
      src="public/uploads/img/Cópia de R.P.Q. Logo (2) 4.png"
      alt="Restaurante Panela Quente" />
    <div class="header-right">
      <h1 class="header-title">
        Bem-vindo <?= htmlspecialchars($_SESSION['nome'] ?? 'Usuário') ?> ao nosso estoque!
      </h1>
      <nav class="nav-pills">
        <a href="index.php?controller=auth&action=dashboard">Menu</a>
        <a href="index.php?controller=fornecedor&action=index">Fornecedores</a>
        <a href="index.php?controller=produto&action=index" class="active">Estoque</a>
        <a href="index.php?controller=venda&action=index">Pedidos</a>
      </nav>
    </div>
  </header>

  <div class="h-divider"></div>

  <!-- Botão para cadastrar novo produto: envia para a página dedicada (node 394:308) -->
  <div class="toolbar">
    <a class="btn-novo" href="index.php?controller=produto&action=form">+ Novo Produto</a>
  </div>

  <div class="table-wrap">

    <!-- HEADER ROW -->
    <div class="table-head">
      <div class="th">Imagem Prod.</div>
      <div class="th">Nome do Produto</div>
      <div class="th th-detalhes">Detalhes</div>
    </div>

    <?php if (empty($produtos)): ?>
      <div class="empty-msg">Nenhum produto cadastrado ainda.</div>
    <?php else: ?>
      <?php foreach ($produtos as $p): ?>
        <div class="produto-row">

          <div class="cell-img">
            <img src="<?= htmlspecialchars(imagemProdutoUrl((int)$p['id'])) ?>"
                 alt="<?= htmlspecialchars($p['nome']) ?>" />
          </div>

          <div class="badge badge-nome"><?= htmlspecialchars($p['nome']) ?></div>
          <div class="badge badge-estoque">Estoque: <?= (int)($p['estoque_qtd'] ?? 0) ?> un.</div>

          <div class="badge badge-status <?= (int)$p['ativo'] === 1 ? 'ativo' : 'inativo' ?>">
            <?= (int)$p['ativo'] === 1 ? 'Ativo' : 'Inativo' ?>
          </div>
          <div class="badge badge-preco"><?= precoFormatado($p['preco'] ?? null) ?></div>

          <?php if ((int)$p['ativo'] === 1): ?>
            <a class="btn-acao btn-inativar"
               href="index.php?controller=produto&action=toggle&id=<?= (int)$p['id'] ?>&ativo=0"
               onclick="return confirm('Inativar este produto?')">Inativar</a>
          <?php else: ?>
            <a class="btn-acao btn-ativar"
               href="index.php?controller=produto&action=toggle&id=<?= (int)$p['id'] ?>&ativo=1">Ativar</a>
          <?php endif; ?>

          <a class="btn-acao btn-excluir"
             href="index.php?controller=produto&action=deletar&id=<?= (int)$p['id'] ?>"
             onclick="return confirm('⚠️ Excluir permanentemente?')">Excluir</a>

          <a class="link-editar"
             href="index.php?controller=produto&action=form&id=<?= (int)$p['id'] ?>">
            Editar Produto
          </a>

        </div>
      <?php endforeach; ?>
    <?php endif; ?>

  </div>

</body>
</html>
