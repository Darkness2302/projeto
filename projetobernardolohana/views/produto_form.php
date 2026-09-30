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
  <link rel="stylesheet" href="public/assets/css/style.css">
</head>
<body data-page="produto-form">

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
            <label class="field-label" for="f-categoria">Categoria</label>
            <select id="f-categoria" name="categoria_id" required>
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
            <label class="field-label" for="f-nome">Nome do Produto</label>
            <input id="f-nome" type="text" name="nome" required placeholder="Nome Produto"
                   value="<?= $editar ? htmlspecialchars($editar['nome']) : '' ?>">
          </div>

          <div class="field-badge">
            <label class="field-label" for="f-descricao">Descrição do produto</label>
            <textarea id="f-descricao" name="descricao" placeholder="Descrição do produto"><?= $editar ? htmlspecialchars($editar['descricao'] ?? '') : '' ?></textarea>
          </div>

          <label class="link-imagem" for="imagem-input">Editar/Colocar Imagem</label>
          <div class="campo-extra-nota">JPG, PNG ou WEBP — até 2 MB</div>
        </div>
      </div>

      <!-- Campos empilhados: Variação, Preço, Fornecedor, Estoque -->
      <div class="stack-fields">
        <div class="field-badge">
          <label class="field-label" for="f-variacao">Variação</label>
          <input id="f-variacao" type="text" name="variacao" placeholder="Variação (ex.: tamanho, sabor)"
                 value="<?= $editar ? htmlspecialchars($editar['variacao'] ?? '') : '' ?>">
        </div>

        <div class="field-badge">
          <label class="field-label" for="f-preco">Preço Unitário (R$)</label>
          <input id="f-preco" type="text" name="preco" inputmode="decimal" placeholder="Preço Unitário (ex.: 28,50)"
                 value="<?= $editar && isset($editar['preco']) ? htmlspecialchars(number_format((float)$editar['preco'], 2, ',', '')) : '' ?>">
        </div>

        <div class="field-badge">
          <?php if (!empty($fornecedores)): ?>
            <label class="field-label" for="f-fornecedor">Fornecedor do Prod.</label>
            <select id="f-fornecedor" name="fornecedor_id">
              <option value="">Fornecedor do Prod. (opcional)</option>
              <?php foreach ($fornecedores as $f): ?>
                <option value="<?= (int)$f['id'] ?>"
                  <?= $editar && (int)($editar['fornecedor_id'] ?? 0) === (int)$f['id'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($f['nome']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          <?php else: ?>
            <!-- Sem fornecedores ativos: aviso com atalho (o campo de texto livre antigo era ignorado pelo controller) -->
            <label class="field-label">Fornecedor do Prod.</label>
            <div class="field-hint">Nenhum fornecedor ativo cadastrado. <a href="index.php?controller=fornecedor&action=index">Cadastrar fornecedor</a></div>
          <?php endif; ?>
        </div>

        <div class="field-badge">
          <label class="field-label" for="f-estoque">Estoque (quantidade)</label>
          <input id="f-estoque" type="number" name="estoque_qtd" min="0" step="1" placeholder="Estoque (quantidade)"
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
