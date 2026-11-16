<?php
// $pedido e $empresa vêm do VendaController::notaFiscal()
//
// ⚠️ Este documento é só uma RECRIAÇÃO VISUAL de nota fiscal — mesmo quando
// o CNPJ é o real do restaurante (configurado em ?controller=empresa), a
// chave de acesso e o código de barras continuam fictícios/decorativos, sem
// validade fiscal nenhuma: não emite, não se comunica com a SEFAZ e não
// serve como documento fiscal de verdade. É só pra ficar com cara de nota.

function nfData(?string $dt): string
{
    return $dt ? date('d/m/Y', strtotime($dt)) : '—';
}
function nfHora(?string $dt): string
{
    return $dt ? date('H:i:s', strtotime($dt)) : '—';
}
function nfValor(?float $v): string
{
    return 'R$ ' . number_format($v ?? 0, 2, ',', '.');
}

/** Dígito verificador de CNPJ (algoritmo padrão mod-11) — usado só pra gerar
 *  um CNPJ fictício com formato/checksum válidos, não é um CNPJ real. */
function nfCnpjDV(array $digitos, array $pesos): int
{
    $soma = 0;
    foreach ($digitos as $i => $d) {
        $soma += $d * $pesos[$i];
    }
    $resto = $soma % 11;
    return $resto < 2 ? 0 : 11 - $resto;
}

/** Usado só enquanto o gerente ainda não configurou o CNPJ de verdade
 *  em ?controller=empresa (ver views/empresa.php). */
function nfCnpjPlaceholder(): string
{
    $base = [1, 2, 3, 4, 5, 6, 7, 8, 0, 0, 0, 1];
    $dv1 = nfCnpjDV($base, [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2]);
    $com1 = array_merge($base, [$dv1]);
    $dv2 = nfCnpjDV($com1, [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2]);
    return implode('', array_merge($com1, [$dv2]));
}

function nfCnpjFormatado(string $digitos): string
{
    return substr($digitos, 0, 2) . '.' . substr($digitos, 2, 3) . '.' . substr($digitos, 5, 3)
         . '/' . substr($digitos, 8, 4) . '-' . substr($digitos, 12, 2);
}

/** Chave de acesso fictícia de 44 dígitos, no formato de uma chave de NFC-e
 *  real (UF+AAMM+CNPJ+modelo+série+número+emissão+código+DV), mas com DV
 *  calculado de forma simplificada — não é o algoritmo oficial da SEFAZ. */
function nfChaveAcesso(string $cnpj, int $pedidoId, string $dataPedido): string
{
    $uf      = '33'; // RJ
    $aamm    = date('ym', strtotime($dataPedido));
    $modelo  = '65'; // NFC-e
    $serie   = '001';
    $numero  = str_pad((string)$pedidoId, 9, '0', STR_PAD_LEFT);
    $emissao = '1';
    $codigoNum = str_pad((string)(crc32('pq-' . $pedidoId) % 100000000), 8, '0', STR_PAD_LEFT);

    $semDv = $uf . $aamm . $cnpj . $modelo . $serie . $numero . $emissao . $codigoNum; // 43 dígitos
    $soma = 0;
    $peso = 2;
    for ($i = strlen($semDv) - 1; $i >= 0; $i--) {
        $soma += ((int)$semDv[$i]) * $peso;
        $peso = $peso === 9 ? 2 : $peso + 1;
    }
    $resto = $soma % 11;
    $dv = ($resto === 0 || $resto === 1) ? 0 : 11 - $resto;

    return $semDv . $dv;
}

function nfChaveFormatada(string $chave): string
{
    return trim(chunk_split($chave, 4, ' '));
}

/** Código de barras decorativo (SVG), gerado a partir do hash da própria
 *  chave — cada nota tem um desenho diferente, mas NENHUM código de barras
 *  aqui é real/escaneável de verdade. */
function nfBarcodeSvg(string $texto): string
{
    $hash = md5($texto);
    $bits = '';
    foreach (str_split($hash) as $h) {
        $bits .= str_pad(base_convert($h, 16, 2), 4, '0', STR_PAD_LEFT);
    }
    // Garante começo/fim com barra "cheia", como um código de barras real
    $bits = '11' . $bits . '11';

    $x = 0;
    $unit = 2.2;
    $bars = '';
    $i = 0;
    $len = strlen($bits);
    while ($i < $len) {
        $bit = $bits[$i];
        $run = 1;
        while ($i + $run < $len && $bits[$i + $run] === $bit) {
            $run++;
        }
        $w = $run * $unit;
        if ($bit === '1') {
            $bars .= '<rect x="' . round($x, 2) . '" y="0" width="' . round($w, 2) . '" height="60" />';
        }
        $x += $w;
        $i += $run;
    }

    return '<svg class="nf-barcode" viewBox="0 0 ' . round($x, 2) . ' 60" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">' . $bars . '</svg>';
}

$itens = $itens ?? [$pedido];
$subtotalNota = 0.0;
$descontoNota = 0.0;
foreach ($itens as $it) {
    $subtotalNota += (float)($it['valor_total'] ?? 0);
    $descontoNota += (float)($it['desconto'] ?? 0);
}
$total = $subtotalNota - $descontoNota;

// $empresa vem do VendaController::notaFiscal() — null se o gerente ainda
// não configurou os dados reais em ?controller=empresa. Nesse caso, usa um
// CNPJ/endereço de exemplo só pra nota não ficar com campo vazio.
$empresaConfigurada = !empty($empresa['cnpj']);
$cnpjDigitos = $empresaConfigurada ? $empresa['cnpj'] : nfCnpjPlaceholder();
$cnpjFmt     = nfCnpjFormatado($cnpjDigitos);
$razaoSocial = $empresaConfigurada && !empty($empresa['razao_social']) ? $empresa['razao_social'] : 'Restaurante Panela Quente Ltda.';
$lugar       = $empresaConfigurada && !empty($empresa['lugar']) ? $empresa['lugar'] : 'Rua das Panelas, 123 — Centro — Rio de Janeiro/RJ — CEP 20000-000';
$telefoneNf  = $empresaConfigurada && !empty($empresa['telefone']) ? $empresa['telefone'] : '(21) 99010-5501';
$chave       = nfChaveAcesso($cnpjDigitos, (int)$pedido['id'], $pedido['data_pedido'] ?? 'now');
$chaveFmt    = nfChaveFormatada($chave);
/* QR Code decorativo: grade 25x25 com os três marcadores de canto,
 * preenchida a partir do hash da chave. Parece um QR real, mas NÃO aponta
 * para nada e não é escaneável de verdade. */
function nfQrSvg(string $texto): string
{
    $n = 25;
    $bits = '';
    $seed = $texto;
    while (strlen($bits) < $n * $n) {
        $seed = md5($seed);
        foreach (str_split($seed) as $h) {
            $bits .= str_pad(base_convert($h, 16, 2), 4, '0', STR_PAD_LEFT);
        }
    }
    $grade = [];
    for ($y = 0; $y < $n; $y++) {
        for ($x = 0; $x < $n; $x++) {
            $grade[$y][$x] = $bits[$y * $n + $x] === '1';
        }
    }
    // marcadores de canto (7x7 com moldura e miolo) + separador branco de 1 célula
    foreach ([[0, 0], [$n - 7, 0], [0, $n - 7]] as [$ox, $oy]) {
        for ($dy = -1; $dy <= 7; $dy++) {
            for ($dx = -1; $dx <= 7; $dx++) {
                $x = $ox + $dx; $y = $oy + $dy;
                if ($x < 0 || $y < 0 || $x >= $n || $y >= $n) continue;
                $dentro = $dx >= 0 && $dx <= 6 && $dy >= 0 && $dy <= 6;
                $grade[$y][$x] = $dentro && ($dx === 0 || $dx === 6 || $dy === 0 || $dy === 6
                                  || ($dx >= 2 && $dx <= 4 && $dy >= 2 && $dy <= 4));
            }
        }
    }
    $rects = '';
    for ($y = 0; $y < $n; $y++) {
        for ($x = 0; $x < $n; $x++) {
            if ($grade[$y][$x]) $rects .= '<rect x="' . $x . '" y="' . $y . '" width="1" height="1"/>';
        }
    }
    return '<svg class="nf-qr" viewBox="0 0 ' . $n . ' ' . $n . '" xmlns="http://www.w3.org/2000/svg" shape-rendering="crispEdges" role="img" aria-label="QR Code da nota">'
         . '<rect width="' . $n . '" height="' . $n . '" fill="#fff"/><g fill="#000">' . $rects . '</g></svg>';
}

$tributos    = $total * 0.1245; // valor aproximado (simulado)
$protocolo   = '133' . str_pad((string)((int)$pedido['id'] * 7919 % 100000000000), 11, '0', STR_PAD_LEFT);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Nota Fiscal <?= htmlspecialchars($pedido['nota_fiscal'] ?? '') ?> – Panela Quente</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;800&family=Roboto+Mono:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="public/assets/css/style.css">
</head>
<body data-page="nota-fiscal">

  <div class="nf-toolbar">
    <?php if (($_SESSION['perfil'] ?? '') === 'cliente'): ?>
      <a class="nf-btn nf-btn-voltar" href="index.php?controller=pedido&action=meus">← Meus pedidos</a>
    <?php else: ?>
      <a class="nf-btn nf-btn-voltar" href="index.php?controller=venda&action=index">← Voltar ao histórico</a>
    <?php endif; ?>
    <button type="button" class="nf-btn nf-btn-imprimir" onclick="window.print()">🖨️ Imprimir</button>
  </div>

  <div class="nf-folha">

    <div class="nf-via-tag">VIA DO CONSUMIDOR</div>

    <div class="nf-cabecalho">
      <img class="nf-logo" src="public/uploads/img/logo-panela-quente.png" alt="Panela Quente">
      <div class="nf-empresa">
        <strong><?= htmlspecialchars($razaoSocial) ?></strong>
        <span><?= htmlspecialchars($lugar) ?></span>
        <span>CNPJ: <?= htmlspecialchars($cnpjFmt) ?> &nbsp;·&nbsp; IE: ISENTO &nbsp;·&nbsp; Tel: <?= htmlspecialchars($telefoneNf) ?></span>
        <?php if (!$empresaConfigurada): ?>
          <span class="nf-cnpj-provisorio">⚙️ CNPJ provisório — configure em "Empresa" (menu do gerente)</span>
        <?php endif; ?>
      </div>
      <div class="nf-numero">
        <span class="nf-numero-label">NFC-e · NOTA FISCAL DE<br>CONSUMIDOR ELETRÔNICA</span>
        <span class="nf-numero-valor">Nº <?= htmlspecialchars($pedido['nota_fiscal'] ?? '—') ?></span>
        <span class="nf-numero-serie">Série 001</span>
      </div>
    </div>

    <div class="nf-aviso-naofiscal">Documento emitido para controle interno do estabelecimento — sem valor fiscal.</div>

    <div class="nf-divider"></div>

    <div class="nf-meta">
      <div><strong>Emissão:</strong> <?= htmlspecialchars(nfData($pedido['data_pedido'] ?? null)) ?> às <?= htmlspecialchars(nfHora($pedido['data_pedido'] ?? null)) ?></div>
      <div><strong>Hora do recebimento:</strong> <?= !empty($pedido['hora_recebimento']) ? htmlspecialchars(nfHora($pedido['hora_recebimento'])) : 'aguardando recebimento' ?></div>
      <div><strong>Pedido:</strong> #<?= (int)$pedido['id'] ?></div>
      <div><strong>Consumidor:</strong> <?= !empty($pedido['cliente_nome']) ? htmlspecialchars($pedido['cliente_nome']) : 'NÃO IDENTIFICADO' ?></div>
    </div>

    <div class="nf-tabela-scroll">
    <table class="nf-tabela">
      <thead>
        <tr>
          <th class="nf-col-cod">Código</th>
          <th>Descrição</th>
          <th>Un.</th>
          <th>Qtd.</th>
          <th>Vl. Unit.</th>
          <th>Vl. Total</th>
        </tr>
      </thead>
      <tbody>
<?php foreach ($itens as $it): ?>
        <tr>
          <td class="nf-col-cod"><?= str_pad((string)($it['produto_id'] ?? 0), 6, '0', STR_PAD_LEFT) ?></td>
          <td><?= htmlspecialchars($it['produto_nome'] ?? '') ?><?php if (!empty($it['variacao'])): ?><br><small><?= htmlspecialchars($it['variacao']) ?></small><?php endif; ?></td>
          <td>UN</td>
          <td><?= (int)($it['quantidade'] ?? 0) ?></td>
          <td><?= htmlspecialchars(nfValor((float)($it['preco_unitario'] ?? 0))) ?></td>
          <td><?= htmlspecialchars(nfValor((float)($it['valor_total'] ?? 0))) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </div>

    <div class="nf-totais">
      <div class="nf-totais-linha"><span>Qtd. de itens</span><span><?= count($itens) ?></span></div>
      <?php if ($descontoNota > 0): ?>
        <div class="nf-totais-linha"><span>Subtotal</span><span><?= htmlspecialchars(nfValor($subtotalNota)) ?></span></div>
        <div class="nf-totais-linha"><span>Desconto (10%)</span><span>− <?= htmlspecialchars(nfValor($descontoNota)) ?></span></div>
      <?php endif; ?>
      <div class="nf-totais-linha nf-totais-final"><span>VALOR TOTAL</span><span><?= htmlspecialchars(nfValor($total)) ?></span></div>
    </div>

    <div class="nf-tributos">Valor aproximado dos tributos: <?= htmlspecialchars(nfValor($tributos)) ?> (12,45%)</div>

    <div class="nf-divider"></div>

    <div class="nf-chave-bloco">
      <span class="nf-chave-label">Chave de acesso</span>
      <span class="nf-chave-valor"><?= htmlspecialchars($chaveFmt) ?></span>
    </div>

    <div class="nf-codigos">
      <div class="nf-barcode-wrap">
        <?= nfBarcodeSvg($chave) ?>
      </div>
      <div class="nf-qr-wrap">
        <?= nfQrSvg($chave) ?>
        <span class="nf-qr-texto">Consulte a autenticidade pela chave de acesso no portal da SEFAZ do seu estado.</span>
      </div>
    </div>

    <div class="nf-protocolo">
      Protocolo de autorização: <?= htmlspecialchars($protocolo) ?> — <?= htmlspecialchars(nfData($pedido['data_pedido'] ?? null)) ?> <?= htmlspecialchars(nfHora($pedido['data_pedido'] ?? null)) ?>
    </div>

    <div class="nf-rodape">
      Obrigado pela preferência! — Panela Quente
      <div class="nf-rodape-disclaimer">Documento não fiscal, gerado apenas para fins de controle interno do pedido.</div>
    </div>
  </div>

</body>
</html>
