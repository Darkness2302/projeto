// Preenche o campo "Lugar" com o endereço ao digitar um CEP (ViaCEP).
// Funciona com o CEP digitado sozinho: 01001-000 ou 01001000.
(function () {
  var input = document.getElementById('f-lugar');
  if (!input) return;

  function buscarCep() {
    var m = (input.value || '').match(/^\s*(\d{5})-?(\d{3})\s*$/);
    if (!m) return;
    var cep = m[1] + m[2];

    fetch('https://viacep.com.br/ws/' + cep + '/json/')
      .then(function (r) { return r.json(); })
      .then(function (d) {
        if (d.erro) return;
        var cidade = d.localidade ? d.localidade + (d.uf ? ' - ' + d.uf : '') : '';
        var partes = [d.logradouro, d.bairro, cidade].filter(Boolean);
        input.value = partes.join(', ') + ' - CEP ' + m[1] + '-' + m[2];
      })
      .catch(function () {});
  }

  input.addEventListener('blur', buscarCep);
  input.addEventListener('change', buscarCep);
})();
