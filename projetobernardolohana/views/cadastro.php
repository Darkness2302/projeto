<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Panela Quente – Sistema de Cadastro</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="public/assets/css/style.css">
</head>
<body data-page="cadastro">

  <header class="site-header">
    <img class="header-logo"
      src="public/uploads/img/Cópia de R.P.Q. Logo (2) 4.png"
      alt="Restaurante Panela Quente" />
    <div class="header-titles">
      <h1>Bem-vindo(a) ao Sistema do Panela Quente</h1>
      <p>Crie sua conta hoje mesmo!</p>
    </div>
  </header>

  <div class="h-divider"></div>

  <div class="split">
    <div class="panel-left">
      <img src="public/uploads/img/image 3.png"
           alt="Pessoas usando dispositivos" />
    </div>

    <div class="panel-right">
      <p class="form-heading">
        Preencha com seus dados<br>para criar a sua conta:
      </p>

      <form action="index.php?controller=usuario&action=store" method="POST">
        <div class="field-group">
          <div class="field">
            <label for="nome">Nome de Usuário</label>
            <input id="nome" type="text" name="nome"
                   placeholder="Digite nome de usuário:" required />
          </div>
          <div class="field">
            <label for="email">Email</label>
            <input id="email" type="email" name="email"
                   placeholder="Digite seu email:" required />
          </div>
          <div class="field">
            <label for="senha">Senha</label>
            <input id="senha" type="password" name="senha"
                   placeholder="Digite sua senha" required />
          </div>
        </div>
        <button type="submit" class="btn-cadastrar">Cadastrar</button>
      </form>

      <div class="login-prompt">
        Já possui conta?
        <a href="index.php?controller=auth&action=form">Clique aqui para logar.</a>
      </div>
    </div>
  </div>

  <section class="privacy-section">
    <div class="privacy-box">
      <p>Não se preocupe, o site da Panela Quente não utilizará os dados colocados acima para nenhum fim lucrativo, eles apenas serão usados para fazer login no site e então observar cada ação tomada na sua conta.</p>
      <p>Caso você veja que uma compra indesejada foi realizada, não consegue conectar ou perdeu sua senha, envie uma mensagem ao nosso pedido de suporte clicando no botão abaixo.</p>
      
    </div>
  </section>

</body>
</html>
