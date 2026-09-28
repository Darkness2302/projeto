<?php
function imagemFornecedorUrl(int $fornecedorId): ?string
{
    $baseFs  = __DIR__ . "/../public/uploads/fornecedores/";
    $baseUrl = "public/uploads/fornecedores/";
    foreach (['jpg', 'png', 'webp'] as $ext) {
        if (file_exists($baseFs . $fornecedorId . '.' . $ext)) {
            return $baseUrl . $fornecedorId . '.' . $ext;
        }
    }
    return null;
}

function recebimentoFormatado(?string $data, ?string $hora): string
{
    if (!$data) return 'Ainda sem registro';
    $dataFmt = date('d/m/Y', strtotime($data));
    $horaFmt = $hora ? substr($hora, 0, 5) : '';
    return trim($dataFmt . ' ' . $horaFmt);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Panela Quente – Fornecedores</title>
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
      --tan:      #f3d8ac;
      --lgreen:   #adf3ac;
      --lblue:    #acdcf3;
      --green-ok: #d4edda;
    }
    body { font-family:'Inter',sans-serif; background:var(--white); color:var(--black); min-height:100vh; }

    /* HEADER */
    .site-header { display:flex; align-items:center; padding:22px 48px 18px 40px; gap:28px; }
    .header-logo { width:120px; height:auto; flex-shrink:0; }
    .header-right { flex:1; display:flex; flex-direction:column; align-items:center; gap:16px; }
    .header-title { font-size:2.4rem; font-weight:600; color:var(--black); text-align:center; }
    .nav-pills { display:flex; gap:16px; align-items:center; flex-wrap:wrap; justify-content:center; }
    .nav-pills a { display:inline-block; padding:9px 34px; background:linear-gradient(90deg, var(--green) 60%, var(--green-dk) 100%); color:var(--black); font-weight:600; font-size:1.25rem; text-decoration:none; border-radius:200px; transition:opacity .15s, transform .15s; }
    .nav-pills a:hover { opacity:.82; transform:translateY(-2px); }
    .nav-pills a.active { background:linear-gradient(90deg, var(--green-dk) 0%, #2d3620 100%); color:#fff; }
    .h-divider { height:1px; background:var(--divider); }

    /* FORM SECTION */
    .form-section { padding:28px 56px 24px; }
    .form-grid {
      display:grid;
      grid-template-columns: 260px 1fr 1fr;
      grid-template-rows: auto auto auto;
      gap:14px 18px;
      align-items:stretch;
    }

    .img-preview {
      grid-column: 1; grid-row: 1 / 3;
      background:var(--gray-img); min-height:190px;
      display:flex; align-items:center; justify-content:center; overflow:hidden;
      cursor:pointer; color:var(--gray-ph); font-size:.9rem; text-align:center; padding:10px;
    }
    .img-preview img { width:100%; height:100%; object-fit:cover; }

    .field-badge input, .field-badge select {
      width:100%; background:#efefef; border:1px solid #000; border-radius:5px;
      padding:14px 18px; font-family:'Inter',sans-serif; font-size:1.1rem; font-weight:600;
      color:var(--black); text-align:center; outline:none;
    }
    .field-badge input::placeholder { color:#444; font-weight:600; }
    .field-badge input:focus { background:#e2e2e2; }

    .fb-cpf      { grid-column: 2; grid-row: 1; }
    .fb-produtos { grid-column: 3; grid-row: 1; }
    .fb-lugar    { grid-column: 2; grid-row: 2; }
    .fb-nome     { grid-column: 3; grid-row: 2; }

    .link-logo {
      grid-column: 1; grid-row: 3;
      background:none; border:none; padding:0; cursor:pointer;
      font-family:'Inter',sans-serif; font-size:1.05rem; font-weight:800; color:var(--teal);
      text-align:left; align-self:start; margin-top:8px;
    }
    .link-logo:hover { text-decoration:underline; }

    .form-actions {
      grid-column: 2 / 4; grid-row: 3;
      display:flex; justify-content:flex-end; align-items:start; gap:16px; margin-top:8px;
    }
    .btn-limpar, .btn-adicionar {
      padding:14px 30px; border:1px solid #000; border-radius:5px;
      font-family:'Inter',sans-serif; font-size:1.1rem; font-weight:600; color:#111;
      cursor:pointer; text-decoration:none; display:inline-flex; align-items:center; justify-content:center;
      transition:opacity .15s, transform .12s;
    }
    .btn-limpar:hover, .btn-adicionar:hover { opacity:.85; transform:translateY(-1px); }
    .btn-limpar { background:var(--lblue); }
    .btn-adicionar { background:var(--lgreen); border:none; }

    /* LIST */
    .list-wrap { padding:8px 56px 64px; }
    .fornecedor-row {
      display:grid;
      grid-template-columns: 230px 1fr 1fr 260px;
      grid-template-rows: auto auto auto;
      gap:12px 16px;
      padding:24px 0;
      border-bottom:1px solid var(--divider);
      align-items:start;
    }
    .fornecedor-row:first-child { padding-top:8px; }

    .row-img {
      grid-column: 1; grid-row: 1 / 3;
      background:var(--gray-img); min-height:190px;
      display:flex; align-items:center; justify-content:center; overflow:hidden;
    }
    .row-img img { width:100%; height:100%; object-fit:cover; }

    .badge {
      background:var(--salmon); border:1px solid #000; border-radius:5px;
      padding:14px 18px; font-size:1.1rem; font-weight:600; color:var(--black);
      display:flex; align-items:center; min-height:56px;
    }
    .badge-nome    { grid-column: 2; grid-row: 1; }
    .badge-cpf     { grid-column: 3; grid-row: 1; }
    .badge-produto { grid-column: 2; grid-row: 2; }

    .status-tag {
      grid-column: 3; grid-row: 2;
      display:flex; align-items:center; gap:10px;
    }
    .tag-ativo   { background:var(--green-ok); color:#155724; border-radius:5px; padding:8px 16px; font-weight:700; text-decoration:none; }
    .tag-inativo { background:#f8d7da; color:#721c24; border-radius:5px; padding:8px 16px; font-weight:700; text-decoration:none; }

    .recebimento {
      grid-column: 2 / 4; grid-row: 3;
    }
    .recebimento .titulo { color:var(--teal); font-weight:800; font-size:1.15rem; }
    .recebimento .data { color:var(--black); font-weight:600; font-size:1.05rem; }

    .row-actions {
      grid-column: 4; grid-row: 1 / 3;
      display:flex; gap:14px; align-items:flex-start; justify-content:flex-end;
    }
    .btn-remover, .btn-editar {
      padding:14px 28px; border:1px solid #000; border-radius:5px;
      font-size:1.1rem; font-weight:600; text-align:center;
      text-decoration:none; cursor:pointer; display:flex; align-items:center; justify-content:center;
      min-height:56px; transition:opacity .15s;
    }
    .btn-remover:hover, .btn-editar:hover { opacity:.82; }
    .btn-remover { background:var(--red); color:#111; }
    .btn-editar  { background:var(--tan); color:#111; }

    .empty-msg { padding:40px; text-align:center; color:#777; font-size:1.1rem; }

    @media (max-width:960px) {
      .site-header { padding:16px 20px; flex-direction:column; }
      .header-title { font-size:1.5rem; }
      .nav-pills a  { font-size:.95rem; padding:6px 20px; }
      .form-section, .list-wrap { padding:20px 16px; }
      .form-grid { grid-template-columns: 1fr 1fr; }
      .img-preview { grid-column: 1 / 3; grid-row: 1; min-height:140px; }
      .fb-cpf      { grid-column: 1; grid-row: 2; }
      .fb-produtos { grid-column: 2; grid-row: 2; }
      .fb-lugar    { grid-column: 1; grid-row: 3; }
      .fb-nome     { grid-column: 2; grid-row: 3; }
      .link-logo   { grid-column: 1; grid-row: 4; }
      .form-actions{ grid-column: 1 / 3; grid-row: 5; justify-content:flex-start; flex-wrap:wrap; }

      .fornecedor-row { grid-template-columns: 90px 1fr 1fr; }
      .row-img { min-height:100px; }
      .row-actions { grid-column: 1 / 4; grid-row: 4; justify-content:flex-start; }
      .recebimento { grid-column: 1 / 4; grid-row: 3; }
      .badge, .btn-remover, .btn-editar { font-size:.85rem; padding:8px; min-height:40px; }
    }
  </style>
</head>
<body>

  <header class="site-header">
    <img class="header-logo"
      src="public/uploads/img/Cópia de R.P.Q. Logo (2) 4.png"
      alt="Restaurante Panela Quente" />
    <div class="header-right">
      <h1 class="header-title">Lista dos Fornecedores Atuais:</h1>
      <nav class="nav-pills">
        <a href="index.php?controller=fornecedor&action=index" class="active">Fornecedores</a>
        <a href="index.php?controller=produto&action=index">Estoque</a>
        <a href="index.php?controller=venda&action=index">Pedidos</a>
      </nav>
    </div>
  </header>

  <div class="h-divider"></div>

  <!-- ══ FORM: ADICIONAR / EDITAR FORNECEDOR ══ -->
  <div class="form-section">
    <form method="post" action="index.php?controller=fornecedor&action=salvar" enctype="multipart/form-data">
      <input type="hidden" name="id" value="<?= $editar ? (int)$editar['id'] : 0 ?>">

      <div class="form-grid">
        <label class="img-preview" for="logo-input" id="logo-preview-box">
          <?php
            $logoAtual = $editar ? imagemFornecedorUrl((int)$editar['id']) : null;
          ?>
          <?php if ($logoAtual): ?>
            <img src="<?= htmlspecialchars($logoAtual) ?>" id="logo-preview-tag" alt="Logo do fornecedor">
          <?php else: ?>
            <span id="logo-preview-placeholder">Sem logo</span>
          <?php endif; ?>
        </label>
        <input type="file" id="logo-input" name="logo" accept="image/png, image/jpeg, image/webp" style="display:none;">

        <div class="field-badge fb-cpf">
          <input type="text" name="cpf" placeholder="CPF"
                 value="<?= $editar ? htmlspecialchars($editar['cpf'] ?? '') : '' ?>">
        </div>
        <div class="field-badge fb-produtos">
          <input type="text" name="produtos" placeholder="Produtos"
                 value="<?= $editar ? htmlspecialchars($editar['produtos'] ?? '') : '' ?>">
        </div>
        <div class="field-badge fb-lugar">
          <input type="text" name="lugar" placeholder="Lugar"
                 value="<?= $editar ? htmlspecialchars($editar['lugar'] ?? '') : '' ?>">
        </div>
        <div class="field-badge fb-nome">
          <input type="text" name="nome" required placeholder="Nome"
                 value="<?= $editar ? htmlspecialchars($editar['nome']) : '' ?>">
        </div>

        <label class="link-logo" for="logo-input">Inserir Logo (Arquivos de Imagem)</label>

        <div class="form-actions">
          <a class="btn-limpar" href="index.php?controller=fornecedor&action=index">Limpar</a>
          <button type="submit" class="btn-adicionar"><?= $editar ? 'Salvar' : 'Adicionar' ?></button>
        </div>
      </div>
    </form>
  </div>

  <div class="h-divider" style="margin:4px 0;"></div>

  <!-- ══ LISTA DE FORNECEDORES ══ -->
  <div class="list-wrap">
    <?php if (empty($fornecedores)): ?>
      <div class="empty-msg">Nenhum fornecedor cadastrado ainda.</div>
    <?php else: ?>
      <?php foreach ($fornecedores as $f): ?>
        <div class="fornecedor-row">

          <div class="row-img">
            <?php $logoUrl = imagemFornecedorUrl((int)$f['id']); ?>
            <?php if ($logoUrl): ?>
              <img src="<?= htmlspecialchars($logoUrl) ?>" alt="Logo de <?= htmlspecialchars($f['nome']) ?>">
            <?php endif; ?>
          </div>

          <div class="badge badge-nome"><?= htmlspecialchars($f['nome']) ?></div>
          <div class="badge badge-cpf"><?= htmlspecialchars($f['cpf'] ?? '—') ?></div>

          <div class="badge badge-produto"><?= htmlspecialchars($f['produtos'] ?? '—') ?></div>
          <div class="status-tag">
            <?php if ((int)$f['ativo'] === 1): ?>
              <a class="tag-ativo" href="index.php?controller=fornecedor&action=toggle&id=<?= (int)$f['id'] ?>&ativo=0">Ativo</a>
            <?php else: ?>
              <a class="tag-inativo" href="index.php?controller=fornecedor&action=toggle&id=<?= (int)$f['id'] ?>&ativo=1">Inativo</a>
            <?php endif; ?>
          </div>

          <div class="recebimento">
            <div class="titulo">Hora de chegada dos produtos - Hora do recebimento</div>
            <div class="data"><?= htmlspecialchars(recebimentoFormatado($f['data_recebimento'] ?? null, $f['hora_recebimento'] ?? null)) ?></div>
          </div>

          <div class="row-actions">
            <a class="btn-remover"
               href="index.php?controller=fornecedor&action=deletar&id=<?= (int)$f['id'] ?>"
               onclick="return confirm('⚠️ Remover este fornecedor permanentemente?')">Remover</a>
            <a class="btn-editar"
               href="index.php?controller=fornecedor&action=index&id=<?= (int)$f['id'] ?>">Editar</a>
          </div>

        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <script>
    // Pré-visualização da logo escolhida, antes de salvar
    document.getElementById('logo-input').addEventListener('change', function (e) {
      const file = e.target.files[0];
      if (!file) return;
      const box = document.getElementById('logo-preview-box');
      const reader = new FileReader();
      reader.onload = function (ev) {
        box.innerHTML = '<img src="' + ev.target.result + '" alt="Pré-visualização">';
      };
      reader.readAsDataURL(file);
    });
  </script>

</body>
</html>
