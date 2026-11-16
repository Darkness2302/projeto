<?php
function precoMov(?float $v): string
{
    return 'R$ ' . number_format($v ?? 0, 2, ',', '.');
}
$filtroAtual = $tipo ?? 'todas';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Panela Quente – Movimentações</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="public/assets/css/style.css">
</head>
<body data-page="produtos">

  <header class="site-header">
    <img class="header-logo" src="public/uploads/img/logo-panela-quente.png" alt="Restaurante Panela Quente" />
    <div class="header-right">
      <h1 class="header-title">Movimentações de mercadoria</h1>
      <nav class="nav-pills">
        <?php if (acl_podeVer('dashboard')): ?><a href="index.php?controller=auth&action=dashboard">Menu</a><?php endif; ?>
        <?php if (acl_podeVer('fornecedor')): ?><a href="index.php?controller=fornecedor&action=index">Fornecedores</a><?php endif; ?>
        <?php if (acl_podeVer('funcionario')): ?><a href="index.php?controller=funcionario&action=index">Funcionários</a><?php endif; ?>
        <a href="index.php?controller=produto&action=index">Estoque</a>
        <a href="index.php?controller=produto&action=movimentos" class="active">Movimentações</a>
        <?php if (acl_podeVer('venda')): ?><a href="index.php?controller=venda&action=index">Pedidos</a><?php endif; ?>
        <?php if (acl_podeVer('empresa')): ?><a href="index.php?controller=empresa&action=index">Empresa</a><?php endif; ?>
      </nav>
    </div>
  </header>

  <div class="h-divider"></div>

  <!-- Registro de entrada de mercadoria (fornecedor entrega produto) -->
  <section class="mov-box">
    <h2 class="mov-titulo">Entrada de mercadoria</h2>

    <?php if ($flashEntrada): ?>
      <div class="mov-flash mov-flash-<?= htmlspecialchars($flashEntrada['tipo']) ?>">
        <?= htmlspecialchars($flashEntrada['msg']) ?>
      </div>
    <?php endif; ?>

    <form class="mov-form" method="post" action="index.php?controller=produto&action=entradaSalvar">
      <label>Produto
        <select name="produto_id" required>
          <option value="">Selecione...</option>
          <?php foreach ($produtos as $pr): ?>
            <option value="<?= (int)$pr['id'] ?>"><?= htmlspecialchars($pr['nome']) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label>Fornecedor
        <select name="fornecedor_id">
          <option value="">Não informado</option>
          <?php foreach ($fornecedores as $f): ?>
            <option value="<?= (int)$f['id'] ?>"><?= htmlspecialchars($f['nome']) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label>Quantidade
        <input type="number" name="quantidade" min="1" step="1" required>
      </label>
      <label>Data
        <input type="date" name="data" value="<?= date('Y-m-d') ?>">
      </label>
      <button type="submit" class="mov-btn">Registrar entrada</button>
    </form>
  </section>

  <section class="mov-box">
    <h2 class="mov-titulo">Estoque por produto</h2>
    <div class="mov-tabela-wrap">
      <table class="mov-tabela">
        <thead>
          <tr>
            <th>Produto</th><th>Estoque atual</th><th>Total que entrou</th>
            <th>Total que saiu</th><th>Última entrada</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($resumo as $r): ?>
            <?php $atual = (int)$r['estoque_qtd']; $classe = $atual <= 0 ? 'estoque-zero' : ($atual <= 10 ? 'estoque-baixo' : ''); ?>
            <tr>
              <td><?= htmlspecialchars($r['nome']) ?></td>
              <td><span class="badge badge-estoque <?= $classe ?>"><?= $atual ?> un.</span></td>
              <td>+<?= (int)$r['total_entrada'] ?></td>
              <td>−<?= (int)$r['total_saida'] ?></td>
              <td><?= $r['ultima_entrada'] ? htmlspecialchars(date('d/m/Y H:i', strtotime($r['ultima_entrada']))) : '—' ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <h2 class="mov-titulo">Movimentações</h2>
    <div class="mov-filtros">
      <a href="index.php?controller=produto&action=movimentos" class="<?= $filtroAtual === 'todas' ? 'ativo' : '' ?>">Todas</a>
      <a href="index.php?controller=produto&action=movimentos&tipo=entrada" class="<?= $filtroAtual === 'entrada' ? 'ativo' : '' ?>">Entradas</a>
      <a href="index.php?controller=produto&action=movimentos&tipo=venda" class="<?= $filtroAtual === 'venda' ? 'ativo' : '' ?>">Saídas (vendas)</a>
    </div>

    <?php if (empty($movimentos)): ?>
      <div class="empty-msg">Nenhuma movimentação registrada.</div>
    <?php else: ?>
      <div class="mov-tabela-wrap">
        <table class="mov-tabela">
          <thead>
            <tr>
              <th>Data / hora</th><th>Tipo</th><th>Produto</th><th>Qtd</th>
              <th>Estoque</th><th>Valor</th><th>Quem enviou / comprou</th><th>Pedido</th>
            </tr>
          </thead>
          <tbody>
            <?php
              // Um pedido (nota) com vários produtos vira UMA linha, com todos os itens listados.
              $grupos = [];
              foreach ($movimentos as $m) {
                $chave = ($m['tipo'] === 'venda' && !empty($m['pedido_id']))
                       ? 'v:' . ($m['nota_fiscal'] ?: $m['pedido_id'])
                       : 'x:' . $m['id'];
                if (!isset($grupos[$chave])) $grupos[$chave] = ['head' => $m, 'itens' => [], 'qtd' => 0, 'valor' => 0.0];
                $grupos[$chave]['itens'][] = $m;
                $grupos[$chave]['qtd']   += (int)$m['quantidade'];
                $grupos[$chave]['valor'] += (float)$m['valor_total'];
              }
            ?>
            <?php foreach ($grupos as $g): $m = $g['head']; $multi = count($g['itens']) > 1; ?>
              <tr>
                <td><?= htmlspecialchars(date('d/m/Y H:i', strtotime($m['criado_em']))) ?></td>
                <td>
                  <span class="mov-tipo mov-tipo-<?= $m['tipo'] ?>">
                    <?= $m['tipo'] === 'entrada' ? 'Entrada' : 'Saída (venda)' ?>
                  </span>
                </td>
                <td>
                  <?php if (!$multi): ?>
                    <?= htmlspecialchars($m['produto_nome']) ?>
                  <?php else: ?>
                    <ul class="mov-itens">
                      <?php foreach ($g['itens'] as $it): ?>
                        <li><?= htmlspecialchars($it['produto_nome']) ?> — <?= (int)$it['quantidade'] ?> un.</li>
                      <?php endforeach; ?>
                    </ul>
                  <?php endif; ?>
                </td>
                <td><?= $m['tipo'] === 'entrada' ? '+' : '−' ?><?= (int)$g['qtd'] ?></td>
                <td>
                  <?php if (!$multi): ?>
                    <?= (int)$m['estoque_antes'] ?> → <?= (int)$m['estoque_depois'] ?>
                  <?php else: ?>
                    <ul class="mov-itens">
                      <?php foreach ($g['itens'] as $it): ?>
                        <li><?= (int)$it['estoque_antes'] ?> → <?= (int)$it['estoque_depois'] ?></li>
                      <?php endforeach; ?>
                    </ul>
                  <?php endif; ?>
                </td>
                <td><?= precoMov((float)$g['valor']) ?></td>
                <td>
                  <?php if ($m['tipo'] === 'entrada'): ?>
                    <strong><?= htmlspecialchars($m['fornecedor_nome'] ?? 'Não informado') ?></strong>
                    <small>(fornecedor)</small>
                  <?php else: ?>
                    <strong><?= htmlspecialchars($m['pessoa'] ?? '—') ?></strong>
                    <small>(comprou / registrou)</small>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if (!empty($m['pedido_id'])): ?>
                    <a href="index.php?controller=venda&action=notaFiscal&id=<?= (int)$m['pedido_id'] ?>"><?= !empty($m['nota_fiscal']) ? htmlspecialchars($m['nota_fiscal']) : '#' . (int)$m['pedido_id'] ?></a>
                  <?php else: ?>—<?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </section>

</body>
</html>
