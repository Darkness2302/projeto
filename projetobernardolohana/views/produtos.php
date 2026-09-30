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
  <link rel="stylesheet" href="public/assets/css/style.css">
</head>
<body data-page="produtos">

  <header class="site-header">
    <img class="header-logo"
      src="public/uploads/img/Cópia de R.P.Q. Logo (2) 4.png"
      alt="Restaurante Panela Quente" />
    <div class="header-right">
      <h1 class="header-title">
        Bem-vindo <?= htmlspecialchars($_SESSION['nome'] ?? 'Usuário') ?> ao nosso estoque!
      </h1>
      <nav class="nav-pills">
        <?php if (acl_podeVer('dashboard')): ?><a href="index.php?controller=auth&action=dashboard">Menu</a><?php endif; ?>
        <?php if (acl_podeVer('fornecedor')): ?><a href="index.php?controller=fornecedor&action=index">Fornecedores</a><?php endif; ?>
        <a href="index.php?controller=produto&action=index" class="active">Estoque</a>
        <?php if (acl_podeVer('venda')): ?><a href="index.php?controller=venda&action=index">Pedidos</a><?php endif; ?>
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
                 alt="<?= htmlspecialchars($p['nome']) ?>" loading="lazy" decoding="async" />
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
