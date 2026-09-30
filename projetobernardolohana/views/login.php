<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Panela Quente – Login</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="public/assets/css/style.css">
</head>
<body data-page="login">

  <header class="site-header">
    <img class="header-logo"
      src="public/uploads/img/Cópia de R.P.Q. Logo (2) 4.png"
      alt="Restaurante Panela Quente" />
    <div class="header-titles">
      <h1>Bem-vindo ao sistema do Panela Quente!</h1>
      <p>Faça login para ter acesso ao site</p>
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
        Preencha com seus dados<br>para acessar a plataforma.
      </p>

      <form method="post" action="/projetobernardolohana/index.php?controller=auth&action=login">
        <div class="field-group">
          <div class="field">
            <label for="email">Email</label>
            <input id="email" type="email" name="email"
                   placeholder="Digite seu email:" required autocomplete="username" />
          </div>
          <div class="field">
            <label for="senha">Senha</label>
            <input id="senha" type="password" name="senha"
                   placeholder="Digite sua senha" required autocomplete="current-password" />
          </div>
        </div>
        <button type="submit" class="btn-entrar">Entrar</button>
      </form>

      <div class="signup-prompt">
        Não possui conta?
        <a href="index.php?controller=usuario&action=create">Clique aqui para cadastrar-se.</a>
      </div>
    </div>
  </div>

  <section class="about-section">
    <h2>Sobre nosso restaurante:</h2>
    <div class="about-box">
      <p>&nbsp;&nbsp;&nbsp;Antes de fazer o seu login, lembre-se da nossa missão: </p>
      <p>&nbsp;&nbsp;&nbsp;O nosso prazer é levar o melhor da culinária local e o conforto da nossa comida caseira a cada cliente. Cada prato que preparamos garante almoços em família memoráveis e pausas de trabalho revigorantes.</p>
      <p>&nbsp;&nbsp;&nbsp;Mais do que isso: o seu esforço diário nos ajuda a apoiar as instituições que combatem a fome de quem mais precisa. </p>
      <p>&nbsp;&nbsp;&nbsp;O seu trabalho alimenta e transforma vidas! </p>
    
    </div>
  </section>

</body>
</html>
