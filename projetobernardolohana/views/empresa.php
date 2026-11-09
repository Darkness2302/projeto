<?php
function empresaCnpjFormatado(string $digitos): string
{
    if (strlen($digitos) !== 14) return $digitos;
    return substr($digitos, 0, 2) . '.' . substr($digitos, 2, 3) . '.' . substr($digitos, 5, 3)
         . '/' . substr($digitos, 8, 4) . '-' . substr($digitos, 12, 2);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Panela Quente – Dados da Empresa</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="public/assets/css/style.css">
</head>
<body data-page="empresa">

  <header class="site-header">
    <img class="header-logo" src="public/uploads/img/logo-panela-quente.png" alt="Restaurante Panela Quente" />
    <div class="header-right">
      <h1 class="header-title">Dados da Empresa (usados na Nota Fiscal)</h1>
      <nav class="nav-pills">
        <?php if (acl_podeVer('dashboard')): ?><a href="index.php?controller=auth&action=dashboard">Menu</a><?php endif; ?>
        <?php if (acl_podeVer('fornecedor')): ?><a href="index.php?controller=fornecedor&action=index">Fornecedores</a><?php endif; ?>
        <?php if (acl_podeVer('funcionario')): ?><a href="index.php?controller=funcionario&action=index">Funcionários</a><?php endif; ?>
        <?php if (acl_podeVer('produto')): ?><a href="index.php?controller=produto&action=index">Estoque</a><?php endif; ?>
        <?php if (acl_podeVer('venda')): ?><a href="index.php?controller=venda&action=index">Pedidos</a><?php endif; ?>
        <a href="index.php?controller=empresa&action=index" class="active">Empresa</a>
      </nav>
    </div>
  </header>

  <div class="h-divider"></div>

  <?php if (!empty($flash)): ?>
    <div class="emp-aviso <?= ($flash['tipo'] ?? '') === 'ok' ? 'ok' : 'erro' ?>"><?= htmlspecialchars($flash['msg'] ?? '') ?></div>
  <?php endif; ?>

  <div class="emp-wrap">
    <p class="emp-intro">
      Digite o CNPJ do restaurante — o sistema busca automaticamente a razão social e o endereço
      na base pública da Receita Federal e já preenche o campo "Lugar" sozinho. Dá pra editar à
      mão depois, se precisar ajustar algo.
    </p>

    <form method="post" action="index.php?controller=empresa&action=salvar">
      <div class="emp-field">
        <label class="emp-label" for="f-cnpj">CNPJ</label>
        <input id="f-cnpj" type="text" name="cnpj" inputmode="numeric" placeholder="00.000.000/0000-00"
               value="<?= htmlspecialchars($empresa ? empresaCnpjFormatado($empresa['cnpj']) : '') ?>" required>
        <span id="cnpj-status" class="emp-cnpj-status"></span>
      </div>

      <div class="emp-field">
        <label class="emp-label" for="f-razao">Razão Social</label>
        <input id="f-razao" type="text" name="razao_social" placeholder="Razão social"
               value="<?= htmlspecialchars($empresa['razao_social'] ?? '') ?>" required>
      </div>

      <div class="emp-field">
        <label class="emp-label" for="f-lugar">Lugar (endereço)</label>
        <input id="f-lugar" type="text" name="lugar" placeholder="Preenchido automaticamente ao digitar o CNPJ"
               value="<?= htmlspecialchars($empresa['lugar'] ?? '') ?>">
      </div>

      <div class="emp-field">
        <label class="emp-label" for="f-telefone">Telefone</label>
        <input id="f-telefone" type="text" name="telefone" placeholder="(21) 99010-5501"
               value="<?= htmlspecialchars($empresa['telefone'] ?? '') ?>">
      </div>

      <button type="submit" class="emp-btn-salvar">Salvar</button>
    </form>
  </div>

  <script>
    const cnpjInput   = document.getElementById('f-cnpj');
    const razaoInput  = document.getElementById('f-razao');
    const lugarInput  = document.getElementById('f-lugar');
    const telInput    = document.getElementById('f-telefone');
    const statusSpan  = document.getElementById('cnpj-status');

    function apenasDigitos(v) { return (v || '').replace(/\D/g, ''); }

    function formatarCnpj(digitos) {
      digitos = digitos.slice(0, 14);
      let out = digitos;
      if (digitos.length > 2)  out = digitos.slice(0,2) + '.' + digitos.slice(2);
      if (digitos.length > 5)  out = out.slice(0,6) + '.' + out.slice(6);
      if (digitos.length > 8)  out = out.slice(0,10) + '/' + out.slice(10);
      if (digitos.length > 12) out = out.slice(0,15) + '-' + out.slice(15);
      return out;
    }

    // mesmo algoritmo (mod-11) usado no servidor, só pra não disparar a busca à toa
    function cnpjValido(digitos) {
      if (digitos.length !== 14) return false;
      if (/^(\d)\1{13}$/.test(digitos)) return false;
      const d = digitos.split('').map(Number);
      const calcDV = (pesos) => {
        let soma = 0;
        pesos.forEach((p, i) => soma += d[i] * p);
        const resto = soma % 11;
        return resto < 2 ? 0 : 11 - resto;
      };
      const dv1 = calcDV([5,4,3,2,9,8,7,6,5,4,3,2]);
      if (dv1 !== d[12]) return false;
      const dv2 = calcDV([6,5,4,3,2,9,8,7,6,5,4,3,2]);
      return dv2 === d[13];
    }

    function formatarCep(cep) {
      cep = apenasDigitos(cep);
      return cep.length === 8 ? cep.slice(0,5) + '-' + cep.slice(5) : cep;
    }

    // Monta a string de "Lugar" a partir da resposta da BrasilAPI
    function montarLugar(dados) {
      const partes = [];
      const log = [dados.descricao_tipo_logradouro, dados.logradouro].filter(Boolean).join(' ').trim();
      let linha1 = log;
      if (dados.numero) linha1 += (linha1 ? ', ' : '') + dados.numero;
      if (dados.complemento) linha1 += ' - ' + dados.complemento;
      if (linha1) partes.push(linha1);
      if (dados.bairro) partes.push(dados.bairro);
      const cidadeUf = [dados.municipio, dados.uf].filter(Boolean).join('/');
      if (cidadeUf) partes.push(cidadeUf);
      if (dados.cep) partes.push('CEP ' + formatarCep(dados.cep));
      return partes.join(' - ');
    }

    function formatarTelefone(ddd, numero) {
      if (!numero) return '';
      numero = apenasDigitos(numero);
      if (!ddd) return numero;
      if (numero.length === 9) return `(${ddd}) ${numero.slice(0,5)}-${numero.slice(5)}`;
      if (numero.length === 8) return `(${ddd}) ${numero.slice(0,4)}-${numero.slice(4)}`;
      return `(${ddd}) ${numero}`;
    }

    let ultimoCnpjBuscado = null;

    async function buscarCnpj(digitos) {
      if (digitos === ultimoCnpjBuscado) return;
      ultimoCnpjBuscado = digitos;

      statusSpan.textContent = 'Buscando dados na Receita Federal...';
      statusSpan.className = 'emp-cnpj-status buscando';

      try {
        const resp = await fetch('https://brasilapi.com.br/api/cnpj/v1/' + digitos);
        if (resp.status === 404) {
          statusSpan.textContent = 'CNPJ não encontrado na base pública — preencha os campos à mão.';
          statusSpan.className = 'emp-cnpj-status erro';
          return;
        }
        if (!resp.ok) throw new Error('status ' + resp.status);

        const dados = await resp.json();
        if (dados.razao_social) razaoInput.value = dados.razao_social;
        const lugar = montarLugar(dados);
        if (lugar) lugarInput.value = lugar;
        const tel = formatarTelefone(dados.ddd_telefone_1 ? dados.ddd_telefone_1.slice(0,2) : '', dados.ddd_telefone_1 ? dados.ddd_telefone_1.slice(2) : '');
        if (tel) telInput.value = tel;

        statusSpan.textContent = '✓ Dados encontrados e preenchidos automaticamente.';
        statusSpan.className = 'emp-cnpj-status ok';
      } catch (e) {
        statusSpan.textContent = 'Não foi possível buscar agora (sem internet ou serviço indisponível) — preencha os campos à mão.';
        statusSpan.className = 'emp-cnpj-status erro';
      }
    }

    cnpjInput.addEventListener('input', () => {
      const digitos = apenasDigitos(cnpjInput.value);
      cnpjInput.value = formatarCnpj(digitos);
      statusSpan.textContent = '';
      statusSpan.className = 'emp-cnpj-status';
      if (digitos.length === 14) {
        if (cnpjValido(digitos)) {
          buscarCnpj(digitos);
        } else {
          statusSpan.textContent = 'CNPJ com dígito verificador inválido.';
          statusSpan.className = 'emp-cnpj-status erro';
        }
      }
    });
  </script>

</body>
</html>
