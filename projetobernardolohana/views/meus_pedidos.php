<?php
function valorPedido(float $v): string { return 'R$ ' . number_format($v, 2, ',', '.'); }
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Panela Quente – Meus pedidos</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="public/assets/css/style.css">
</head>
<body data-page="meus-pedidos">
  <header class="car-header">
    <img class="car-logo" src="public/uploads/img/logo-panela-quente.png" alt="Restaurante Panela Quente" />
    <h1 class="car-titulo">Meus pedidos</h1>
    <nav class="car-nav">
      <a class="cli-btn cli-btn-sec" href="index.php?controller=auth&action=dashboard">← Voltar ao cardápio</a>
      <a class="cli-btn cli-btn-sec" href="index.php?controller=pedido&action=carrinho">Ver meu pedido</a>
      <a class="car-sair" href="index.php?controller=auth&action=logout">Sair</a>
    </nav>
  </header>

  <main class="car-main">
    <?php if ($flash): ?>
      <div class="cli-flash cli-flash-<?= htmlspecialchars($flash['tipo']) ?>"><?= htmlspecialchars($flash['msg']) ?></div>
    <?php endif; ?>

    <?php if (empty($pedidos)): ?>
      <div class="car-vazio">Você ainda não fez nenhum pedido. <a href="index.php?controller=auth&action=dashboard">Ver o cardápio</a></div>
    <?php else: ?>
      <div class="car-lista">
        <?php foreach ($pedidos as $p): ?>
          <div class="car-item">
            <div class="car-item-info">
              <strong><?= htmlspecialchars($p['nota_fiscal']) ?></strong>
              <span><?= htmlspecialchars(date('d/m/Y H:i', strtotime($p['data_pedido']))) ?> · <?= (int)$p['itens'] ?> item(ns)</span>
            </div>
            <div class="car-item-total"><?= valorPedido((float)$p['total']) ?></div>
            <a class="car-remover" href="index.php?controller=venda&action=notaFiscal&id=<?= (int)$p['primeiro_id'] ?>">Ver nota</a>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </main>
</body>
</html>
