<?php
function valorCarrinho(float $v): string { return 'R$ ' . number_format($v, 2, ',', '.'); }
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Panela Quente – Seu pedido</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="public/assets/css/style.css">
</head>
<body data-page="carrinho">
  <header class="car-header">
    <img class="car-logo" src="public/uploads/img/logo-panela-quente.png" alt="Restaurante Panela Quente" />
    <h1 class="car-titulo">Seu pedido</h1>
    <nav class="car-nav">
      <a class="cli-btn cli-btn-sec" href="index.php?controller=auth&action=dashboard">← Voltar ao cardápio</a>
      <a class="cli-btn cli-btn-sec" href="index.php?controller=pedido&action=meus">Meus pedidos</a>
      <a class="car-sair" href="index.php?controller=auth&action=logout">Sair</a>
    </nav>
  </header>

  <main class="car-main">
    <?php if ($flash): ?>
      <div class="cli-flash cli-flash-<?= htmlspecialchars($flash['tipo']) ?>"><?= htmlspecialchars($flash['msg']) ?></div>
    <?php endif; ?>

    <?php if (empty($itens)): ?>
      <div class="car-vazio">Seu pedido está vazio. <a href="index.php?controller=auth&action=dashboard">Escolher pratos</a></div>
    <?php else: ?>
      <div class="car-lista">
        <?php foreach ($itens as $it): ?>
          <div class="car-item">
            <div class="car-item-info">
              <strong><?= htmlspecialchars($it['nome']) ?></strong>
              <?php if (!empty($it['variacao_texto'])): ?><small><?= htmlspecialchars($it['variacao_texto']) ?></small><?php endif; ?>
              <span><?= (int)$it['quantidade'] ?> × <?= valorCarrinho((float)$it['preco']) ?></span>
            </div>
            <div class="car-item-total"><?= valorCarrinho((float)$it['subtotal']) ?></div>
            <a class="car-remover" href="index.php?controller=pedido&action=remover&chave=<?= urlencode($it['chave']) ?>">Remover</a>
          </div>
        <?php endforeach; ?>
      </div>

      <?php if ($temDesconto): ?>
        <div class="car-promo car-promo-ok">Desconto de <?= (int)PedidoController::DESCONTO_PERCENTUAL ?>% aplicado: pedido com <?= $qtdItens ?> itens.</div>
      <?php else: ?>
        <div class="car-promo">Faltam <?= $faltam ?> item(ns) para ganhar <?= (int)PedidoController::DESCONTO_PERCENTUAL ?>% de desconto (a partir de <?= (int)PedidoController::DESCONTO_QTD_MIN ?> itens).</div>
      <?php endif; ?>

      <div class="car-total car-total-detalhe">
        <span>Subtotal</span>
        <span><?= valorCarrinho((float)$subtotal) ?></span>
      </div>
      <?php if ($temDesconto): ?>
        <div class="car-total car-total-detalhe">
          <span>Desconto (<?= (int)PedidoController::DESCONTO_PERCENTUAL ?>%)</span>
          <span>− <?= valorCarrinho((float)$desconto) ?></span>
        </div>
      <?php endif; ?>
      <div class="car-total">
        <span>Total</span>
        <strong><?= valorCarrinho((float)$total) ?></strong>
      </div>

      <form method="post" action="index.php?controller=pedido&action=finalizar">
        <button type="submit" class="cli-btn cli-btn-grande">Finalizar pedido</button>
      </form>
    <?php endif; ?>
  </main>
</body>
</html>
