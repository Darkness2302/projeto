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
      src="public/uploads/img/logo-panela-quente.png"
      alt="Restaurante Panela Quente" />
    <div class="header-right">
      <h1 class="header-title">Acesse o histórico de pedidos aqui:</h1>
      <nav class="nav-pills">
        <?php if (acl_podeVer('dashboard')): ?><a href="index.php?controller=auth&action=dashboard">Menu</a><?php endif; ?>
        <?php if (acl_podeVer('fornecedor')): ?><a href="index.php?controller=fornecedor&action=index">Fornecedores</a><?php endif; ?>
        <?php if (acl_podeVer('funcionario')): ?><a href="index.php?controller=funcionario&action=index">Funcionários</a><?php endif; ?>
        <?php if (acl_podeVer('produto')): ?><a href="index.php?controller=produto&action=index">Estoque</a><?php endif; ?>
        <a href="index.php?controller=venda&action=index" class="active">Pedidos</a>
        <?php if (acl_podeVer('empresa')): ?><a href="index.php?controller=empresa&action=index">Empresa</a><?php endif; ?>
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

        <button type="submit" class="btn-adicionar">Adicionar ao pedido</button>
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
          <div class="field" id="campo-variacao" style="display:none">
            <label class="field-label" for="f-variacao">Variação</label>
            <select id="f-variacao" name="variacao_id"></select>
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

  <!-- ══ PEDIDO EM MONTAGEM: vários produtos, uma nota só ══ -->
  <div class="montagem-box">
    <h2 class="montagem-titulo">Pedido em montagem</h2>
    <?php if (empty($rascunho)): ?>
      <p class="montagem-vazio">Nenhum item ainda. Escolha um produto e clique em "Adicionar ao pedido" — repita para quantos produtos quiser.</p>
    <?php else: ?>
      <ul class="montagem-lista">
        <?php foreach ($rascunho as $i => $r): ?>
          <li>
            <span><?= (int)$r['quantidade'] ?>× <?= htmlspecialchars($r['produto_nome']) ?><?php if (!empty($r['variacao'])): ?> (<?= htmlspecialchars($r['variacao']) ?>)<?php endif; ?>
              — <?= htmlspecialchars(valorFormatado((int)$r['quantidade'] * (float)$r['preco_unitario'])) ?></span>
            <a class="montagem-remover" href="index.php?controller=venda&action=removerItem&i=<?= (int)$i ?>">remover</a>
          </li>
        <?php endforeach; ?>
      </ul>
      <div class="montagem-resumo">
        <div>Itens: <strong><?= (int)$rascQtd ?></strong></div>
        <div>Subtotal: <strong><?= htmlspecialchars(valorFormatado($rascTotal)) ?></strong></div>
        <?php if ($rascDesconto > 0): ?>
          <div class="montagem-desc">Desconto de 10%: −<?= htmlspecialchars(valorFormatado($rascDesconto)) ?> → Total: <strong><?= htmlspecialchars(valorFormatado($rascTotal - $rascDesconto)) ?></strong></div>
        <?php else: ?>
          <div class="montagem-falta">Faltam <?= (int)$rascFaltam ?> item(ns) para ganhar 10% de desconto.</div>
        <?php endif; ?>
      </div>
    <?php endif; ?>
    <div class="montagem-acoes">
      <button type="submit" form="form-pedido" class="btn-adicionar"
              formaction="index.php?controller=venda&action=finalizar" formnovalidate
              <?= empty($rascunho) ? 'disabled' : '' ?>>Finalizar pedido</button>
      <a class="btn-limpar" href="index.php?controller=venda&action=cancelarPedido"
         onclick="return confirm('Cancelar o pedido em montagem?');">Cancelar pedido</a>
    </div>
  </div>

  <!-- ══ HISTÓRICO (o pedido mais recente aparece primeiro) ══ -->
  <div class="list-wrap">
    <?php if (empty($pedidos)): ?>
      <div class="empty-state"><p>Nenhum pedido registrado ainda.</p></div>
    <?php else: ?>
      <?php
        // Um pedido com vários produtos = UM cartão, identificado pela nota fiscal.
        $grupos = [];
        foreach ($pedidos as $p) {
          $chave = !empty($p['nota_fiscal']) ? $p['nota_fiscal'] : 'id' . $p['id'];
          if (!isset($grupos[$chave])) {
            $grupos[$chave] = ['head' => $p, 'itens' => [], 'qtd' => 0, 'total' => 0.0];
          }
          $grupos[$chave]['itens'][] = $p;
          $grupos[$chave]['qtd']   += (int)$p['quantidade'];
          $grupos[$chave]['total'] += (float)$p['valor_total'] - (float)($p['desconto'] ?? 0);
        }
      ?>
      <?php foreach ($grupos as $g): $pedido = $g['head']; ?>
        <div class="pedido-row">

          <div class="pedido-img">
            <img src="<?= htmlspecialchars(imagemProdutoPedido(isset($pedido['produto_id']) ? (int)$pedido['produto_id'] : null)) ?>"
                 alt="<?= htmlspecialchars($pedido['produto_nome'] ?? 'Produto') ?>" loading="lazy" decoding="async">
          </div>

          <div class="pedido-body">
            <div class="pedido-badges">
              <div class="badge badge-nome">
                <span class="pid"><?= htmlspecialchars(!empty($pedido['nota_fiscal']) ? $pedido['nota_fiscal'] : '#' . (int)$pedido['id']) ?></span>
                <?php if (count($g['itens']) === 1): ?>
                  <?= htmlspecialchars($pedido['produto_nome'] ?? '') ?><?php if (!empty($pedido['variacao'])): ?><span class="pedido-var"> — <?= htmlspecialchars($pedido['variacao']) ?></span><?php endif; ?>
                <?php else: ?>
                  <?= count($g['itens']) ?> produtos
                <?php endif; ?>
              </div>
              <div class="badge">Quantidade: <?= (int)$g['qtd'] ?></div>
              <div class="badge">Total: <?= htmlspecialchars(valorFormatado((float)$g['total'])) ?></div>
            </div>
            <?php if (count($g['itens']) > 1): ?>
              <ul class="pedido-itens">
                <?php foreach ($g['itens'] as $it): ?>
                  <li><?= (int)$it['quantidade'] ?>× <?= htmlspecialchars($it['produto_nome'] ?? '') ?><?php if (!empty($it['variacao'])): ?> (<?= htmlspecialchars($it['variacao']) ?>)<?php endif; ?></li>
                <?php endforeach; ?>
              </ul>
            <?php endif; ?>
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
    // Variações: mostra só as opções do produto escolhido, com preço
    const VARIACOES = <?= json_encode(array_values($variacoes), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;
    const campoVar = document.getElementById('campo-variacao');
    const selVar   = document.getElementById('f-variacao');
    function atualizarVariacoes() {
      if (!sel || !campoVar || !selVar) return;
      const pid = parseInt(sel.value || '0', 10);
      const opcoes = VARIACOES.filter(v => parseInt(v.produto_id, 10) === pid);
      selVar.innerHTML = '';
      const vazio = document.createElement('option');
      vazio.value = '';
      vazio.textContent = 'Escolha a variação...';
      selVar.appendChild(vazio);
      opcoes.forEach(v => {
        const o = document.createElement('option');
        o.value = v.id;
        o.textContent = v.nome + ' — R$ ' + Number(v.preco).toFixed(2).replace('.', ',');
        selVar.appendChild(o);
      });
      campoVar.style.display = opcoes.length ? '' : 'none';
      selVar.required = opcoes.length > 0;
    }
    if (sel) {
      sel.addEventListener('change', atualizarVariacoes);
      atualizarVariacoes();
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
