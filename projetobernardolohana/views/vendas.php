<?php
function imagemProdutoPedido(?int $produtoId): string
{
    $fallback = "public/assets/img/produto_sem_foto.png";
    if (!$produtoId) return $fallback;
    $baseFs  = __DIR__ . "/../public/uploads/produtos/";
    $baseUrl = "public/uploads/produtos/";
    foreach (['jpg', 'png', 'webp'] as $ext) {
        if (file_exists($baseFs . $produtoId . '.' . $ext)) {
            return $baseUrl . $produtoId . '.' . $ext;
        }
    }
    return $fallback;
}

function pedidoData(?string $dt): string
{
    return $dt ? date('d/m/Y', strtotime($dt)) : '—';
}

function pedidoHora(?string $v): string
{
    return $v ? date('H:i', strtotime($v)) : '';
}

function valorFormatado(?float $v): string
{
    return 'R$ ' . number_format($v ?? 0, 2, ',', '.');
}
$semFoto = "public/assets/img/produto_sem_foto.png";
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
  <link rel="stylesheet" href="public/assets/css/style.css">
</head>
<body data-page="vendas">

  <header class="site-header">
    <img class="header-logo"
      src="public/uploads/img/Cópia de R.P.Q. Logo (2) 4.png"
      alt="Restaurante Panela Quente" />
    <div class="header-right">
      <h1 class="header-title">Acesse o histórico de pedidos aqui:</h1>
      <nav class="nav-pills">
        <?php if (acl_podeVer('dashboard')): ?><a href="index.php?controller=auth&action=dashboard">Menu</a><?php endif; ?>
        <?php if (acl_podeVer('fornecedor')): ?><a href="index.php?controller=fornecedor&action=index">Fornecedores</a><?php endif; ?>
        <?php if (acl_podeVer('produto')): ?><a href="index.php?controller=produto&action=index">Estoque</a><?php endif; ?>
        <a href="index.php?controller=venda&action=index" class="active">Pedidos</a>
      </nav>
    </div>
  </header>

  <div class="h-divider"></div>

  <?php if (!empty($erroBanco)): ?>
    <div class="aviso erro"><?= htmlspecialchars($erroBanco) ?></div>
  <?php endif; ?>
  <?php if (!empty($flash)): ?>
    <div class="aviso <?= ($flash['tipo'] ?? '') === 'ok' ? 'ok' : 'erro' ?>"><?= htmlspecialchars($flash['msg'] ?? '') ?></div>
  <?php endif; ?>

  <!-- ══ REGISTRAR / REMOVER PEDIDO ══ -->
  <div class="form-section">
    <form id="form-pedido" method="post" action="index.php?controller=venda&action=adicionar">
      <div class="form-grid">

        <div class="img-preview">
          <img id="img-preview-tag" src="<?= htmlspecialchars($semFoto) ?>" alt="Foto do produto escolhido">
        </div>

        <div class="field fb-id">
          <label class="field-label" for="f-id">ID Pedido (usado só para Remover)</label>
          <input id="f-id" type="number" name="id_pedido" min="1" placeholder="ID Pedido">
        </div>
        <div class="field fb-data">
          <label class="field-label" for="f-data">Hora/Data</label>
          <input id="f-data" type="datetime-local" name="data_pedido" value="<?= date('Y-m-d\TH:i') ?>">
        </div>
        <div class="field fb-qtd">
          <label class="field-label" for="f-qtd">Quantidade</label>
          <input id="f-qtd" type="number" name="quantidade" min="1" step="1" placeholder="Quantidade" required>
        </div>

        <button type="submit" class="btn-adicionar">Adicionar</button>
        <button type="submit" class="btn-remover"
                formaction="index.php?controller=venda&action=remover" formnovalidate
                onclick="return confirmarRemocao()">Remover</button>
        <a class="btn-limpar" href="index.php?controller=venda&action=index">Limpar</a>

        <div class="extra-row">
          <div class="field">
            <label class="field-label" for="f-produto">Produto</label>
            <?php if (!empty($produtosSel)): ?>
              <select id="f-produto" name="produto_id" required>
                <option value="" data-img="<?= htmlspecialchars($semFoto) ?>">Escolha o produto...</option>
                <?php foreach ($produtosSel as $pr): ?>
                  <option value="<?= (int)$pr['id'] ?>" data-img="<?= htmlspecialchars(imagemProdutoPedido((int)$pr['id'])) ?>">
                    <?= htmlspecialchars($pr['nome']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            <?php else: ?>
              <div class="field-hint">Nenhum produto ativo. <a href="index.php?controller=produto&action=index">Ir para o Estoque</a></div>
            <?php endif; ?>
          </div>
          <div class="field">
            <label class="field-label" for="f-receb">Hora do recebimento</label>
            <input id="f-receb" type="time" name="hora_recebimento">
          </div>
        </div>

        <div class="nota-auto-hint">🧾 A nota fiscal é gerada automaticamente ao registrar o pedido — não precisa digitar nada.</div>

      </div>
    </form>
  </div>

  <div class="h-divider"></div>

  <!-- ══ HISTÓRICO (o pedido mais recente aparece primeiro) ══ -->
  <div class="list-wrap">
    <?php if (empty($pedidos)): ?>
      <div class="empty-state"><p>Nenhum pedido registrado ainda.</p></div>
    <?php else: ?>
      <?php foreach ($pedidos as $pedido): ?>
        <div class="pedido-row">

          <div class="pedido-img">
            <img src="<?= htmlspecialchars(imagemProdutoPedido(isset($pedido['produto_id']) ? (int)$pedido['produto_id'] : null)) ?>"
                 alt="<?= htmlspecialchars($pedido['produto_nome'] ?? 'Produto') ?>" loading="lazy" decoding="async">
          </div>

          <div class="pedido-body">
            <div class="pedido-badges">
              <div class="badge badge-nome">
                <span class="pid">#<?= (int)$pedido['id'] ?></span><?= htmlspecialchars($pedido['produto_nome'] ?? '') ?>
              </div>
              <div class="badge">Quantidade: <?= (int)$pedido['quantidade'] ?></div>
              <div class="badge">Total: <?= htmlspecialchars(valorFormatado(isset($pedido['valor_total']) ? (float)$pedido['valor_total'] : null)) ?></div>
            </div>
            <div class="pedido-quando">
              <div class="quando-hora">
                <?= htmlspecialchars(pedidoHora($pedido['data_pedido'] ?? null)) ?>
                –
                <?= !empty($pedido['hora_recebimento']) ? htmlspecialchars(pedidoHora($pedido['hora_recebimento'])) : 'aguardando recebimento' ?>
              </div>
              <div class="quando-data"><?= htmlspecialchars(pedidoData($pedido['data_pedido'] ?? null)) ?></div>
            </div>
            <?php if (!empty($pedido['nota_fiscal'])): ?>
              <a class="link-nota" href="index.php?controller=venda&action=notaFiscal&id=<?= (int)$pedido['id'] ?>">
                🧾 Ver Nota Fiscal (<?= htmlspecialchars($pedido['nota_fiscal']) ?>)
              </a>
            <?php endif; ?>
          </div>

        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <script>
    // Mostra a foto do produto escolhido na caixa cinza do formulário
    const sel = document.getElementById('f-produto');
    if (sel) {
      sel.addEventListener('change', function () {
        const opt = sel.options[sel.selectedIndex];
        document.getElementById('img-preview-tag').src = opt.dataset.img;
      });
    }
    // Remover exige o ID do pedido e confirmação
    function confirmarRemocao() {
      const id = document.getElementById('f-id').value.trim();
      if (!id) { alert('Digite o ID do pedido (mostrado como #número no histórico) para remover.'); return false; }
      return confirm('Remover permanentemente o pedido #' + id + '?');
    }
  </script>

</body>
</html>
