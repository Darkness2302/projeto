<?php
function imagemProdutoUrlForm(?int $produtoId): ?string
{
    if (!$produtoId) return null;
    $baseFs  = __DIR__ . "/../public/uploads/produtos/";
    $baseUrl = "public/uploads/produtos/";
    foreach (['jpg', 'png', 'webp'] as $ext) {
        if (file_exists($baseFs . $produtoId . '.' . $ext)) {
            return $baseUrl . $produtoId . '.' . $ext;
        }
    }
    return null;
}

$produtoId   = $editar ? (int)$editar['id'] : null;
$imagemAtual = imagemProdutoUrlForm($produtoId);
$titulo      = $editar ? 'Editando Produto #' . $produtoId : 'Adicionando Produto...';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Panela Quente – <?= $editar ? 'Editar' : 'Adicionar' ?> Produto</title>
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
      --gray-desc:#c8c6c5;
      --gray-ph:  #676767;
      --white:    #ffffff;
      --black:    #111111;
      --divider:  #c8c8c8;
      --yellow:   #f3efac;
      --lgreen:   #adf3ac;
    }
    body { font-family:'Inter',sans-serif; background:var(--white); color:var(--black); min-height:100vh; }

    /* HEADER (sem pills de navegação — conforme o Figma desta página) */
    .site-header { display:flex; align-items:center; padding:20px 48px 16px 40px; gap:28px; }
    .header-logo { width:112px; height:auto; flex-shrink:0; }
    .header-title { font-size:1.85rem; font-weight:500; color:var(--black); }
    .h-divider { height:1px; background:var(--divider); }

    /* FORM LAYOUT */
    .form-wrap { padding:36px 56px 60px; max-width:1440px; margin:0 auto; }

    .top-grid {
      display:grid;
      grid-template-columns: minmax(260px, 460px) 1fr;
      gap:28px;
      align-items:start;
      margin-bottom: 28px;
    }

    .img-preview {
      background:var(--gray-img); border:1px solid #000;
      width:100%; aspect-ratio: 558 / 397; min-height:260px;
      display:flex; align-items:center; justify-content:center;
      overflow:hidden; color:var(--gray-ph); font-size:1rem; text-align:center; padding:12px;
    }
    .img-preview img { width:100%; height:100%; object-fit:cover; }

    .right-col { display:flex; flex-direction:column; gap:16px; }

    .field-badge input,
    .field-badge select {
      width:100%; background:var(--salmon); border:1px solid #000; border-radius:5px;
      padding:16px 20px; font-family:'Inter',sans-serif; font-size:1.2rem; color:var(--black);
      outline:none;
    }
    .field-badge input::placeholder { color:#555; }
    .field-badge input:focus, .field-badge select:focus { background:#eeae96; }

    .field-badge textarea {
      width:100%; background:var(--gray-desc); border:1px solid #000; border-radius:5px;
      padding:16px 20px; font-family:'Inter',sans-serif; font-size:1.2rem; color:var(--black);
      outline:none; resize:vertical; min-height:180px;
    }

    .link-imagem {
      background:none; border:none; padding:0; cursor:pointer;
      font-family:'Inter',sans-serif; font-size:1.6rem; font-weight:800; color:var(--teal);
      text-align:left; text-decoration:none; align-self:flex-start;
    }
    .link-imagem:hover { text-decoration:underline; }

    .stack-fields { display:flex; flex-direction:column; gap:16px; margin-bottom: 30px; }

    .form-actions { display:flex; justify-content:center; gap:24px; }
    .btn-voltar, .btn-salvar {
      padding:16px 46px; border:1px solid #000; border-radius:5px;
      font-family:'Inter',sans-serif; font-size:1.3rem; font-weight:400; color:var(--black);
      cursor:pointer; text-decoration:none; display:inline-flex; align-items:center; justify-content:center;
      transition:opacity .15s, transform .12s;
    }
    .btn-voltar:hover, .btn-salvar:hover { opacity:.85; transform:translateY(-1px); }
    .btn-voltar { background:var(--yellow); }
    .btn-salvar { background:var(--lgreen); }

    .campo-extra-nota { font-size:.8rem; color:#888; margin-top:-6px; }

    @media (max-width:860px) {
      .site-header { padding:16px 20px; }
      .form-wrap   { padding:20px 16px 40px; }
      .header-title{ font-size:1.3rem; }
      .top-grid    { grid-template-columns: 1fr; }
      .field-badge input, .field-badge select, .field-badge textarea { font-size:1rem; padding:12px 14px; }
      .link-imagem { font-size:1.2rem; }
      .btn-voltar, .btn-salvar { padding:12px 26px; font-size:1rem; }
    }
  </style>
</head>
<body>

  <header class="site-header">
    <img class="header-logo"
      src="public/uploads/img/Cópia de R.P.Q. Logo (2) 4.png"
      alt="Restaurante Panela Quente" />
    <h1 class="header-title"><?= htmlspecialchars($titulo) ?></h1>
  </header>

  <div class="h-divider"></div>

  <div class="form-wrap">
    <form method="post" action="index.php?controller=produto&action=salvar" enctype="multipart/form-data">
      <input type="hidden" name="id" value="<?= $produtoId ?? 0 ?>">

      <div class="top-grid">
        <!-- Coluna esquerda: imagem -->
        <div>
          <label class="img-preview" for="imagem-input" id="img-preview-box">
            <?php if ($imagemAtual): ?>
              <img src="<?= htmlspecialchars($imagemAtual) ?>" id="img-preview-tag" alt="Imagem do produto">
            <?php else: ?>
              <span id="img-preview-placeholder">Nenhuma imagem selecionada</span>
            <?php endif; ?>
          </label>
          <input type="file" id="imagem-input" name="imagem" accept="image/png, image/jpeg, image/webp" style="display:none;">
        </div>

        <!-- Coluna direita: categoria, nome, descrição, link de imagem -->
        <div class="right-col">

          <!-- Campo adicional (não presente no frame do Figma, necessário pelo banco: categoria é obrigatória) -->
          <div class="field-badge">
            <select name="categoria_id" required>
              <option value="">Categoria...</option>
              <?php foreach ($categorias as $c): ?>
                <option value="<?= (int)$c['id'] ?>"
                  <?= $editar && (int)$editar['categoria_id'] === (int)$c['id'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($c['nome']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="field-badge">
            <input type="text" name="nome" required placeholder="Nome Produto"
                   value="<?= $editar ? htmlspecialchars($editar['nome']) : '' ?>">
          </div>

          <div class="field-badge">
            <textarea name="descricao" placeholder="Descrição do produto"><?= $editar ? htmlspecialchars($editar['descricao'] ?? '') : '' ?></textarea>
          </div>

          <label class="link-imagem" for="imagem-input">Editar/Colocar Imagem</label>
          <div class="campo-extra-nota">JPG, PNG ou WEBP — até 2 MB</div>
        </div>
      </div>

      <!-- Campos empilhados: Variação, Preço, Fornecedor, Estoque -->
      <div class="stack-fields">
        <div class="field-badge">
          <input type="text" name="variacao" placeholder="Variação (ex.: tamanho, sabor)"
                 value="<?= $editar ? htmlspecialchars($editar['variacao'] ?? '') : '' ?>">
        </div>

        <div class="field-badge">
          <input type="text" name="preco" inputmode="decimal" placeholder="Preço Unitário (ex.: 28,50)"
                 value="<?= $editar && isset($editar['preco']) ? htmlspecialchars(number_format((float)$editar['preco'], 2, ',', '')) : '' ?>">
        </div>

        <div class="field-badge">
          <?php if (!empty($fornecedores)): ?>
            <select name="fornecedor_id">
              <option value="">Fornecedor do Prod. (opcional)</option>
              <?php foreach ($fornecedores as $f): ?>
                <option value="<?= (int)$f['id'] ?>"
                  <?= $editar && (int)($editar['fornecedor_id'] ?? 0) === (int)$f['id'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($f['nome']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          <?php else: ?>
            <!-- Fallback: tabela fornecedor ainda não cadastrada / sem fornecedores ativos -->
            <input type="text" name="fornecedor_nome_livre" placeholder="Fornecedor do Prod. (opcional)"
                   value="<?= $editar ? htmlspecialchars($editar['fornecedor_id'] ?? '') : '' ?>">
          <?php endif; ?>
        </div>

        <div class="field-badge">
          <input type="number" name="estoque_qtd" min="0" step="1" placeholder="Estoque (quantidade)"
                 value="<?= $editar ? (int)($editar['estoque_qtd'] ?? 0) : '' ?>">
        </div>
      </div>

      <div class="form-actions">
        <a class="btn-voltar" href="index.php?controller=produto&action=index">Voltar</a>
        <button type="submit" class="btn-salvar">Salvar</button>
      </div>
    </form>
  </div>

  <script>
    // Pré-visualização da imagem escolhida, antes de salvar
    document.getElementById('imagem-input').addEventListener('change', function (e) {
      const file = e.target.files[0];
      if (!file) return;
      const box = document.getElementById('img-preview-box');
      const reader = new FileReader();
      reader.onload = function (ev) {
        box.innerHTML = '<img src="' + ev.target.result + '" alt="Pré-visualização">';
      };
      reader.readAsDataURL(file);
    });
  </script>

</body>
</html>
