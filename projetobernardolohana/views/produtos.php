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
    }
    body { font-family:'Inter',sans-serif; background:var(--white); color:var(--black); min-height:100vh; }

    /* HEADER */
    .site-header { display:flex; align-items:center; padding:20px 48px 16px 40px; }
    .header-logo { width:112px; height:auto; flex-shrink:0; }
    .header-right { flex:1; display:flex; flex-direction:column; align-items:center; gap:14px; }
    .header-title { font-size:1.85rem; font-weight:500; color:var(--black); text-align:center; }
    .nav-pills { display:flex; gap:16px; align-items:center; }
    .nav-pills a { display:inline-block; padding:7px 36px; background:linear-gradient(90deg, var(--green) 60%, var(--green-dk) 100%); color:var(--black); font-weight:600; font-size:1.2rem; text-decoration:none; border-radius:200px; transition:opacity .15s, transform .15s; }
    .nav-pills a:hover { opacity:.82; transform:translateY(-2px); }
    .nav-pills a.active { background:linear-gradient(90deg, var(--green-dk) 0%, #2d3620 100%); color:#fff; }
    .h-divider { height:1px; background:var(--divider); }

    /* FORM SECTION */
    .form-section { padding:28px 56px 24px; }
    .form-card { background:#f9f6f4; border-radius:15px; padding:32px 36px; }
    .form-card h2 { font-size:1.4rem; font-weight:600; color:var(--black); margin-bottom:22px; }
    .form-grid { display:grid; grid-template-columns:1fr 1fr; gap:16px 28px; }
    .form-grid.full { grid-template-columns:1fr; }
    .field { display:flex; flex-direction:column; gap:6px; }
    .field label { font-size:1rem; font-weight:500; color:var(--black); }
    .field input, .field select, .field textarea {
      background:var(--green); border:none; border-radius:9px;
      box-shadow:0 3px 4px rgba(0,0,0,.15); padding:12px 18px;
      font-family:'Inter',sans-serif; font-size:1rem; color:#333; outline:none; width:100%;
      transition:background .2s, box-shadow .2s;
    }
    .field input::placeholder, .field textarea::placeholder { color:var(--gray-ph); }
    .field input:focus, .field select:focus, .field textarea:focus { background:#b5ce97; box-shadow:0 3px 10px rgba(80,92,65,.3); }
    .field input[type="file"] { background:#e5f0d8; padding:10px 14px; }
    .field small { font-size:.85rem; color:#666; }
    .form-actions { display:flex; gap:14px; margin-top:22px; }
    .btn-salvar {
      padding:12px 32px; background:linear-gradient(90deg, var(--green) 60%, var(--green-dk) 100%);
      border:none; border-radius:200px; font-family:'Inter',sans-serif; font-size:1rem; font-weight:600;
      color:#111; cursor:pointer; box-shadow:0 3px 6px rgba(0,0,0,.2); transition:opacity .15s, transform .12s;
    }
    .btn-salvar:hover { opacity:.88; transform:translateY(-1px); }
    .btn-limpar { padding:12px 32px; background:var(--salmon); border:none; border-radius:200px; font-family:'Inter',sans-serif; font-size:1rem; font-weight:600; color:#111; cursor:pointer; text-decoration:none; display:inline-flex; align-items:center; transition:opacity .15s; }
    .btn-limpar:hover { opacity:.8; }

    /* TABLE */
    .table-wrap { padding:8px 56px 64px; overflow-x:auto; }
    .stock-table {
      width:100%; min-width:900px; display:grid;
      grid-template-columns: 180px 1fr 220px 120px 230px;
      column-gap:16px;
    }

    /* HEADER ROW */
    .th { background:var(--salmon); border-radius:5px; padding:12px 16px; font-size:1.1rem; font-weight:400; color:var(--black); margin-bottom:18px; display:flex; align-items:center; }
    .th-estoque { grid-column: 3 / 6; justify-content:center; }

    /* PRODUCT ROW */
    .row { display:contents; }
    .row > * { margin-bottom:14px; }

    .cell-img { background:var(--gray-img); height:130px; display:flex; align-items:center; justify-content:center; overflow:hidden; }
    .cell-img img { width:100%; height:100%; object-fit:cover; }

    .cell-name { display:flex; flex-direction:column; gap:8px; justify-content:center; }
    .badge-name { background:var(--salmon); border-radius:5px; padding:12px 16px; font-size:1rem; font-weight:400; color:var(--black); min-height:52px; display:flex; align-items:center; }
    .link-edit { font-size:1.05rem; font-weight:800; color:var(--teal); text-decoration:none; transition:opacity .15s; }
    .link-edit:hover { opacity:.7; text-decoration:underline; }

    .cell-cat, .cell-status { display:flex; align-items:center; }
    .badge-cat { background:var(--salmon); border-radius:5px; padding:12px 16px; font-size:.95rem; color:var(--black); width:100%; min-height:52px; display:flex; align-items:center; }

    .tag-ativo   { background:#d4edda; color:#155724; border-radius:5px; padding:6px 12px; font-size:.9rem; font-weight:600; }
    .tag-inativo { background:#f8d7da; color:#721c24; border-radius:5px; padding:6px 12px; font-size:.9rem; font-weight:600; }

    .cell-acoes { display:flex; flex-direction:column; gap:6px; justify-content:center; }
    .btn-inativar { padding:7px 14px; background:var(--salmon); border:none; border-radius:8px; font-size:.85rem; font-weight:600; color:#7a3b00; cursor:pointer; text-decoration:none; text-align:center; transition:opacity .15s; }
    .btn-inativar:hover { opacity:.8; }
    .btn-ativar { padding:7px 14px; background:#d4edda; border:none; border-radius:8px; font-size:.85rem; font-weight:600; color:#155724; cursor:pointer; text-decoration:none; text-align:center; transition:opacity .15s; }
    .btn-ativar:hover { opacity:.8; }
    .btn-excluir { padding:7px 14px; background:#f8d7da; border:none; border-radius:8px; font-size:.85rem; font-weight:600; color:#721c24; cursor:pointer; text-decoration:none; text-align:center; transition:opacity .15s; }
    .btn-excluir:hover { opacity:.8; }

    @media (max-width:860px) {
      .site-header { padding:16px 20px; }
      .table-wrap  { padding:20px 16px 48px; }
      .form-section { padding:20px 16px 16px; }
      .header-title { font-size:1.3rem; }
      .nav-pills a  { font-size:.95rem; padding:6px 20px; }
      .stock-table  { grid-template-columns:100px 1fr 120px 80px 160px; column-gap:8px; min-width:540px; }
      .form-grid    { grid-template-columns:1fr; }
    }
  </style>
</head>
<body>

  <header class="site-header">
    <img class="header-logo"
      src="https://www.figma.com/api/mcp/asset/3ec2b4da-f034-4822-a784-78bc70eace04"
      alt="Restaurante Panela Quente" />
    <div class="header-right">
      <h1 class="header-title">
        Bem-vindo <?= htmlspecialchars($_SESSION['nome'] ?? 'Usuário') ?> ao nosso estoque!
      </h1>
      <nav class="nav-pills">
        <a href="/projetobernardolohana/index.php?controller=auth&action=dashboard">Menu</a>
        <a href="/projetobernardolohana/index.php?controller=produto&action=index" class="active">Estoque</a>
        <a href="/projetobernardolohana/index.php?controller=venda&action=index">Pedidos</a>
      </nav>
    </div>
  </header>

  <div class="h-divider"></div>

  <!-- ══ FORM: CADASTRAR / EDITAR PRODUTO ══ -->
  <div class="form-section">
    <div class="form-card">
      <h2><?= $editar ? 'Editar Produto #' . (int)$editar['id'] : 'Cadastrar Novo Produto' ?></h2>
      <form method="post" action="index.php?controller=produto&action=salvar" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= $editar ? (int)$editar['id'] : 0 ?>">
        <div class="form-grid">
          <div class="field">
            <label>Categoria</label>
            <select name="categoria_id" required>
              <option value="">Selecione...</option>
              <?php foreach ($categorias as $c): ?>
                <option value="<?= (int)$c['id'] ?>"
                  <?= $editar && (int)$editar['categoria_id'] === (int)$c['id'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($c['nome']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="field">
            <label>Nome do Produto</label>
            <input type="text" name="nome" required placeholder="Nome do produto"
                   value="<?= $editar ? htmlspecialchars($editar['nome']) : '' ?>" />
          </div>
          <div class="field">
            <label>Descrição (opcional)</label>
            <textarea name="descricao" rows="2" placeholder="Descrição do produto"><?= $editar ? htmlspecialchars($editar['descricao'] ?? '') : '' ?></textarea>
          </div>
          <div class="field">
            <label>Imagem (opcional)</label>
            <input type="file" name="imagem" accept="image/png, image/jpeg, image/webp" />
            <small>JPG, PNG ou WEBP — até 2 MB</small>
          </div>
        </div>
        <div class="form-actions">
          <button type="submit" class="btn-salvar">Salvar</button>
          <a href="index.php?controller=produto&action=index" class="btn-limpar">Limpar</a>
        </div>
      </form>
    </div>
  </div>

  <div class="h-divider" style="margin:4px 0;"></div>

  <!-- ══ TABLE ══ -->
  <div class="table-wrap">
    <div class="stock-table">

      <!-- HEADER ROW -->
      <div class="th">Imagem Prod.</div>
      <div class="th">Nome do Produto</div>
      <div class="th th-estoque">Detalhes (Categoria · Status · Ações)</div>

      <!-- PRODUCT ROWS -->
      <?php foreach ($produtos as $p): ?>
        <div class="row">

          <div class="cell-img">
            <img src="<?= htmlspecialchars(imagemProdutoUrl((int)$p['id'])) ?>"
                 alt="<?= htmlspecialchars($p['nome']) ?>" />
          </div>

          <div class="cell-name">
            <div class="badge-name"><?= htmlspecialchars($p['nome']) ?></div>
            <a class="link-edit"
               href="index.php?controller=produto&action=index&id=<?= (int)$p['id'] ?>">
              Editar Produto
            </a>
          </div>

          <div class="cell-cat">
            <div class="badge-cat"><?= htmlspecialchars($p['categoria_nome']) ?></div>
          </div>

          <div class="cell-status">
            <?php if ((int)$p['ativo'] === 1): ?>
              <span class="tag-ativo">Ativo</span>
            <?php else: ?>
              <span class="tag-inativo">Inativo</span>
            <?php endif; ?>
          </div>

          <div class="cell-acoes">
            <?php if ((int)$p['ativo'] === 1): ?>
              <a class="btn-inativar"
                 href="index.php?controller=produto&action=toggle&id=<?= (int)$p['id'] ?>&ativo=0"
                 onclick="return confirm('Inativar este produto?')">Inativar</a>
            <?php else: ?>
              <a class="btn-ativar"
                 href="index.php?controller=produto&action=toggle&id=<?= (int)$p['id'] ?>&ativo=1">Ativar</a>
            <?php endif; ?>
            <a class="btn-excluir"
               href="index.php?controller=produto&action=deletar&id=<?= (int)$p['id'] ?>"
               onclick="return confirm('⚠️ Excluir permanentemente?')">Excluir</a>
          </div>

        </div>
      <?php endforeach; ?>

    </div>
  </div>

</body>
</html>
