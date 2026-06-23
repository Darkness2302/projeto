<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Panela Quente – Login</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;800&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    :root {
      --salmon:  #f3bfac;
      --green:   #a8c288;
      --green-dk:#505c41;
      --teal:    #1f99ac;
      --gray-ph: #676767;
      --white:   #ffffff;
      --black:   #111111;
      --divider: #c8c8c8;
    }
    body { font-family: 'Inter', sans-serif; background: var(--white); color: var(--black); min-height: 100vh; }

    /* HEADER */
    .site-header { display: flex; align-items: center; gap: 0; padding: 24px 48px 20px 48px; position: relative; }
    .header-logo { width: 108px; height: auto; flex-shrink: 0; }
    .header-titles { flex: 1; text-align: center; }
    .header-titles h1 { font-size: 2.5rem; font-weight: 500; line-height: 1.2; color: var(--black); }
    .header-titles p  { font-size: 1.5rem; font-weight: 400; color: var(--black); margin-top: 6px; }

    .h-divider { height: 1px; background: var(--divider); width: 100%; }

    /* SPLIT */
    .split { display: grid; grid-template-columns: 52.15fr 47.85fr; min-height: 580px; position: relative; }
    .split::after { content:''; position:absolute; left:52.15%; top:0; bottom:0; width:1px; background:var(--divider); }

    .panel-left { display:flex; align-items:center; justify-content:center; padding:32px 24px 32px 8px; }
    .panel-left img { width:100%; max-width:640px; height:auto; object-fit:cover; }

    .panel-right { padding:36px 64px 48px 56px; display:flex; flex-direction:column; justify-content:flex-start; gap:0; }
    .form-heading { font-size:1.8rem; font-weight:500; line-height:1.4; color:var(--black); margin-bottom:32px; }

    .field-group { display:flex; flex-direction:column; gap:20px; margin-bottom:24px; }
    .field { display:flex; flex-direction:column; gap:6px; }
    .field label { font-size:1.3rem; font-weight:400; color:var(--black); }
    .field input {
      background: var(--green); border:none; border-radius:11px;
      box-shadow: 0 4px 4px rgba(0,0,0,.25); padding:18px 24px;
      font-family:'Inter',sans-serif; font-size:1.25rem; color:#333; outline:none; width:100%;
      transition: background .2s, box-shadow .2s, transform .12s;
    }
    .field input::placeholder { color:var(--gray-ph); }
    .field input:focus { background:#b5ce97; box-shadow:0 4px 14px rgba(80,92,65,.3); transform:translateY(-1px); }

    .btn-entrar {
      display:block; width:100%; padding:16px 24px; margin-bottom:20px;
      background: linear-gradient(90deg, var(--green) 60%, var(--green-dk) 100%);
      border:none; border-radius:11px; cursor:pointer;
      font-family:'Inter',sans-serif; font-size:1.25rem; font-weight:600; color:#111;
      box-shadow:0 4px 4px rgba(0,0,0,.2); transition:opacity .15s, transform .12s;
    }
    .btn-entrar:hover { opacity:.88; transform:translateY(-1px); }

    .signup-prompt { font-size:1.3rem; font-weight:400; color:var(--black); line-height:1.6; }
    .signup-prompt a { display:block; font-size:1.3rem; font-weight:800; color:var(--teal); text-decoration:none; transition:opacity .15s; }
    .signup-prompt a:hover { opacity:.75; text-decoration:underline; }

    /* ABOUT */
    .about-section { padding:56px 64px 72px; }
    .about-section h2 { font-size:1.75rem; font-weight:500; text-align:center; margin-bottom:28px; color:var(--black); }
    .about-box { background:var(--salmon); border-radius:15px; padding:44px 56px; font-size:1.35rem; font-weight:400; line-height:1.75; color:#111; }
    .about-box p + p { margin-top:16px; }

    @media (max-width:900px) {
      .split { grid-template-columns:1fr; }
      .split::after { display:none; }
      .panel-left { display:none; }
      .panel-right { padding:36px 32px 48px; }
      .site-header { flex-direction:column; text-align:center; padding:24px 24px 16px; }
      .header-titles h1 { font-size:1.7rem; }
      .header-titles p  { font-size:1.1rem; }
      .about-section { padding:40px 24px 56px; }
      .about-box { padding:28px 28px; font-size:1.05rem; }
    }
  </style>
</head>
<body>

  <header class="site-header">
    <img class="header-logo"
      src="https://www.figma.com/api/mcp/asset/b35d3513-00e5-42d7-b7d3-59a1d1c6dc08"
      alt="Restaurante Panela Quente" />
    <div class="header-titles">
      <h1>Bem-vindo ao Panela Quente</h1>
      <p>Faça login para ter acesso ao site</p>
    </div>
  </header>

  <div class="h-divider"></div>

  <div class="split">
    <div class="panel-left">
      <img src="https://www.figma.com/api/mcp/asset/f8207c49-6875-4fd8-846c-1941bed20a2c"
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
      <p>&nbsp;&nbsp;&nbsp;Somos um restaurante de comida caseira, oferecemos preços acessíveis, ambiente confortável e climatizado, além de contribuirmos para instituições que levam a nutrição e ajudam a sanar a fome de pessoas necessitadas.</p>
      <p>&nbsp;&nbsp;&nbsp;Nosso prazer é levar o melhor da culinária local até sua mesa, fazendo seus almoços em família, e pausas do trabalho, memoráveis e acolhendo ideias que buscam a melhora de nosso estabelecimento e nossos serviços.</p>
      <p>&nbsp;&nbsp;&nbsp;Temos orgulho em servir à você com o melhor da Panela Quente.</p>
    </div>
  </section>

</body>
</html>
