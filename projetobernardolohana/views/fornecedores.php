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
  <link rel="stylesheet" href="public/assets/css/style.css">
</head>
<body data-page="fornecedores">

  <header class="site-header">
    <img class="header-logo"
      src="public/uploads/img/Cópia de R.P.Q. Logo (2) 4.png"
      alt="Restaurante Panela Quente" />
    <div class="header-right">
      <h1 class="header-title">Lista dos Fornecedores Atuais:</h1>
      <nav class="nav-pills">
        <?php if (acl_podeVer('dashboard')): ?><a href="index.php?controller=auth&action=dashboard">Menu</a><?php endif; ?>
        <a href="index.php?controller=fornecedor&action=index" class="active">Fornecedores</a>
        <?php if (acl_podeVer('produto')): ?><a href="index.php?controller=produto&action=index">Estoque</a><?php endif; ?>
        <?php if (acl_podeVer('venda')): ?><a href="index.php?controller=venda&action=index">Pedidos</a><?php endif; ?>
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
          <label class="field-label" for="f-cpf">CPF</label>
          <input id="f-cpf" type="text" name="cpf" placeholder="CPF"
                 value="<?= $editar ? htmlspecialchars($editar['cpf'] ?? '') : '' ?>">
        </div>
        <div class="field-badge fb-produtos">
          <label class="field-label" for="f-produtos">Produtos</label>
          <input id="f-produtos" type="text" name="produtos" placeholder="Produtos"
                 value="<?= $editar ? htmlspecialchars($editar['produtos'] ?? '') : '' ?>">
        </div>
        <div class="field-badge fb-lugar">
          <label class="field-label" for="f-lugar">Lugar</label>
          <input id="f-lugar" type="text" name="lugar" placeholder="Lugar"
                 value="<?= $editar ? htmlspecialchars($editar['lugar'] ?? '') : '' ?>">
        </div>
        <div class="field-badge fb-nome">
          <label class="field-label" for="f-nome">Nome</label>
          <input id="f-nome" type="text" name="nome" required placeholder="Nome"
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
              <img src="<?= htmlspecialchars($logoUrl) ?>" alt="Logo de <?= htmlspecialchars($f['nome']) ?>" loading="lazy" decoding="async">
            <?php endif; ?>
          </div>

          <div class="badge badge-nome"><?= htmlspecialchars($f['nome']) ?></div>
          <div class="badge badge-cpf"><?= htmlspecialchars($f['cpf'] ?? '—') ?></div>

          <div class="badge badge-produto"><?= htmlspecialchars($f['produtos'] ?? '—') ?></div>
          <div class="status-tag">
            <?php if ((int)$f['ativo'] === 1): ?>
              <a class="tag-ativo" title="Clique para inativar" href="index.php?controller=fornecedor&action=toggle&id=<?= (int)$f['id'] ?>&ativo=0">Ativo</a>
            <?php else: ?>
              <a class="tag-inativo" title="Clique para ativar" href="index.php?controller=fornecedor&action=toggle&id=<?= (int)$f['id'] ?>&ativo=1">Inativo</a>
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
