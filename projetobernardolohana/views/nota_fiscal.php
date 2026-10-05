<?php
// $pedido vem do VendaController::notaFiscal()
function nfData(?string $dt): string
{
    return $dt ? date('d/m/Y', strtotime($dt)) : '—';
}
function nfHora(?string $dt): string
{
    return $dt ? date('H:i', strtotime($dt)) : '—';
}
function nfValor(?float $v): string
{
    return 'R$ ' . number_format($v ?? 0, 2, ',', '.');
}
$precoUnit = (float)($pedido['preco_unitario'] ?? 0);
$qtd       = (int)($pedido['quantidade'] ?? 0);
$total     = (float)($pedido['valor_total'] ?? ($precoUnit * $qtd));
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Nota Fiscal <?= htmlspecialchars($pedido['nota_fiscal'] ?? '') ?> – Panela Quente</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="public/assets/css/style.css">
</head>
<body data-page="nota-fiscal">

  <div class="nf-toolbar">
    <a class="nf-btn nf-btn-voltar" href="index.php?controller=venda&action=index">← Voltar ao histórico</a>
    <button type="button" class="nf-btn nf-btn-imprimir" onclick="window.print()">🖨️ Imprimir</button>
  </div>

  <div class="nf-folha">
    <div class="nf-cabecalho">
      <img class="nf-logo" src="public/uploads/img/Cópia de R.P.Q. Logo (2) 4.png" alt="Panela Quente">
      <div class="nf-empresa">
        <strong>Restaurante Panela Quente</strong>
        <span>(21) 99010-5501</span>
      </div>
      <div class="nf-numero">
        <span class="nf-numero-label">NOTA FISCAL</span>
        <span class="nf-numero-valor"><?= htmlspecialchars($pedido['nota_fiscal'] ?? '—') ?></span>
      </div>
    </div>

    <div class="nf-divider"></div>

    <div class="nf-meta">
      <div><strong>Data do pedido:</strong> <?= htmlspecialchars(nfData($pedido['data_pedido'] ?? null)) ?> às <?= htmlspecialchars(nfHora($pedido['data_pedido'] ?? null)) ?></div>
      <div><strong>Hora do recebimento:</strong> <?= !empty($pedido['hora_recebimento']) ? htmlspecialchars(nfHora($pedido['hora_recebimento'])) : 'aguardando recebimento' ?></div>
      <div><strong>Pedido:</strong> #<?= (int)$pedido['id'] ?></div>
    </div>

    <table class="nf-tabela">
      <thead>
        <tr>
          <th>Produto</th>
          <th>Qtd.</th>
          <th>Preço Unit.</th>
          <th>Subtotal</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td><?= htmlspecialchars($pedido['produto_nome'] ?? '') ?></td>
          <td><?= $qtd ?></td>
          <td><?= htmlspecialchars(nfValor($precoUnit)) ?></td>
          <td><?= htmlspecialchars(nfValor($total)) ?></td>
        </tr>
      </tbody>
      <tfoot>
        <tr>
          <td colspan="3">TOTAL</td>
          <td><?= htmlspecialchars(nfValor($total)) ?></td>
        </tr>
      </tfoot>
    </table>

    <div class="nf-rodape">
      Obrigado pela preferência! — Panela Quente
    </div>
  </div>

</body>
</html>
