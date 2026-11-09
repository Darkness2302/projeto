<?php
function expedienteFormatado(?string $inicio, ?string $fim): string
{
    $i = $inicio ? substr($inicio, 0, 5) : '—';
    $f = $fim ? substr($fim, 0, 5) : '—';
    return $i . ' às ' . $f;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Panela Quente – Funcionários</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="public/assets/css/style.css">
</head>
<body data-page="funcionarios">

  <header class="site-header">
    <img class="header-logo"
      src="public/uploads/img/logo-panela-quente.png"
      alt="Restaurante Panela Quente" />
    <div class="header-right">
      <h1 class="header-title">Lista dos Funcionários Atuais:</h1>
      <nav class="nav-pills">
        <?php if (acl_podeVer('dashboard')): ?><a href="index.php?controller=auth&action=dashboard">Menu</a><?php endif; ?>
        <?php if (acl_podeVer('fornecedor')): ?><a href="index.php?controller=fornecedor&action=index">Fornecedores</a><?php endif; ?>
        <a href="index.php?controller=funcionario&action=index" class="active">Funcionários</a>
        <?php if (acl_podeVer('produto')): ?><a href="index.php?controller=produto&action=index">Estoque</a><?php endif; ?>
        <?php if (acl_podeVer('venda')): ?><a href="index.php?controller=venda&action=index">Pedidos</a><?php endif; ?>
        <?php if (acl_podeVer('empresa')): ?><a href="index.php?controller=empresa&action=index">Empresa</a><?php endif; ?>
      </nav>
    </div>
  </header>

  <div class="h-divider"></div>

  <!-- ══ FORM: ADICIONAR / EDITAR FUNCIONÁRIO ══ -->
  <div class="form-section">
    <form method="post" action="index.php?controller=funcionario&action=salvar">
      <input type="hidden" name="id" value="<?= $editar ? (int)$editar['id'] : 0 ?>">

      <div class="form-grid">
        <div class="field-badge fb-cpf">
          <label class="field-label" for="f-cpf">CPF</label>
          <input id="f-cpf" type="text" name="cpf" placeholder="CPF"
                 value="<?= $editar ? htmlspecialchars($editar['cpf'] ?? '') : '' ?>">
        </div>
        <div class="field-badge fb-produtos">
          <label class="field-label" for="f-cargo">Cargo</label>
          <select id="f-cargo" name="cargo_id">
            <option value="">Sem cargo</option>
            <?php foreach ($cargos as $c): ?>
              <option value="<?= (int)$c['id'] ?>"
                <?= $editar && (int)($editar['cargo_id'] ?? 0) === (int)$c['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($c['nome']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="field-badge fb-lugar">
          <label class="field-label" for="f-lugar">Lugar</label>
          <input id="f-lugar" type="text" name="lugar" placeholder="Lugar"
                 value="<?= $editar ? htmlspecialchars($editar['lugar'] ?? '') : '' ?>">
        </div>
        <div class="field-badge fb-inicio">
          <label class="field-label" for="f-inicio">Início do expediente</label>
          <input id="f-inicio" type="time" name="hora_inicio"
                 value="<?= $editar && $editar['hora_inicio'] ? substr($editar['hora_inicio'], 0, 5) : '' ?>">
        </div>
        <div class="field-badge fb-fim">
          <label class="field-label" for="f-fim">Fim do expediente</label>
          <input id="f-fim" type="time" name="hora_fim"
                 value="<?= $editar && $editar['hora_fim'] ? substr($editar['hora_fim'], 0, 5) : '' ?>">
        </div>
        <div class="field-badge fb-nome">
          <label class="field-label" for="f-nome">Nome</label>
          <input id="f-nome" type="text" name="nome" required placeholder="Nome"
                 value="<?= $editar ? htmlspecialchars($editar['nome']) : '' ?>">
        </div>

        <div class="form-actions">
          <a class="btn-limpar" href="index.php?controller=funcionario&action=index">Limpar</a>
          <button type="submit" class="btn-adicionar"><?= $editar ? 'Salvar' : 'Adicionar' ?></button>
        </div>
      </div>
    </form>
  </div>

  <!-- ══ CARGOS ══ -->
  <div class="cargos-box">
    <div class="cargos-titulo">Cargos</div>
    <?php if (!empty($_SESSION['flash_cargo'])): ?>
      <div class="cargos-aviso"><?= htmlspecialchars($_SESSION['flash_cargo']) ?></div>
      <?php unset($_SESSION['flash_cargo']); ?>
    <?php endif; ?>
    <form method="post" action="index.php?controller=funcionario&action=salvarCargo" class="cargos-form">
      <input type="text" name="nome_cargo" required placeholder="Novo cargo (ex.: Cozinheiro)" maxlength="60">
      <button type="submit" class="btn-adicionar">Criar cargo</button>
    </form>
    <div class="cargos-lista">
      <?php if (empty($cargos)): ?>
        <span class="cargos-vazio">Nenhum cargo cadastrado.</span>
      <?php else: ?>
        <?php foreach ($cargos as $c): ?>
          <span class="cargo-chip">
            <?= htmlspecialchars($c['nome']) ?>
            <a href="index.php?controller=funcionario&action=deletarCargo&id=<?= (int)$c['id'] ?>"
               title="Remover cargo"
               onclick="return confirm('Remover este cargo? Os funcionários com ele ficarão sem cargo.')">×</a>
          </span>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

  <div class="h-divider" style="margin:4px 0;"></div>

  <!-- ══ LISTA DE FUNCIONÁRIOS ══ -->
  <div class="list-wrap">
    <?php if (empty($funcionarios)): ?>
      <div class="empty-msg">Nenhum funcionário cadastrado ainda.</div>
    <?php else: ?>
      <?php foreach ($funcionarios as $f): ?>
        <div class="funcionario-row">

          <div class="badge badge-nome"><?= htmlspecialchars($f['nome']) ?></div>
          <div class="badge badge-cpf"><?= htmlspecialchars($f['cpf'] ?? '—') ?></div>
          <div class="badge badge-produto"><?= htmlspecialchars($f['cargo_nome'] ?? '—') ?></div>

          <div class="status-tag">
            <?php if ((int)$f['ativo'] === 1): ?>
              <a class="tag-ativo" title="Clique para inativar" href="index.php?controller=funcionario&action=toggle&id=<?= (int)$f['id'] ?>&ativo=0">Ativo</a>
            <?php else: ?>
              <a class="tag-inativo" title="Clique para ativar" href="index.php?controller=funcionario&action=toggle&id=<?= (int)$f['id'] ?>&ativo=1">Inativo</a>
            <?php endif; ?>
          </div>

          <div class="recebimento">
            <div class="titulo">Expediente</div>
            <div class="data"><?= htmlspecialchars(expedienteFormatado($f['hora_inicio'] ?? null, $f['hora_fim'] ?? null)) ?></div>
          </div>

          <div class="row-actions">
            <a class="btn-remover"
               href="index.php?controller=funcionario&action=deletar&id=<?= (int)$f['id'] ?>"
               onclick="return confirm('⚠️ Remover este funcionário permanentemente?')">Remover</a>
            <a class="btn-editar"
               href="index.php?controller=funcionario&action=index&id=<?= (int)$f['id'] ?>">Editar</a>
          </div>

        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

</body>
</html>
