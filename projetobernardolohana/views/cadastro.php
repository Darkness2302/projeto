<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Panela Quente – Sistema de Cadastro</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;800&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    :root {
      --salmon:   #f3bfac;
      --green:    #a8c288;
      --green-dk: #505c41;
      --teal:     #1f99ac;
      --gray-ph:  #676767;
      --gray-btn: #d9d9d9;
      --white:    #ffffff;
      --black:    #111111;
      --divider:  #c8c8c8;
    }
    body { font-family:'Inter',sans-serif; background:var(--white); color:var(--black); min-height:100vh; }

    /* HEADER */
    .site-header { display:flex; align-items:center; padding:24px 48px 20px 48px; }
    .header-logo { width:108px; height:auto; flex-shrink:0; }
    .header-titles { flex:1; text-align:center; }
    .header-titles h1 { font-size:2.5rem; font-weight:500; line-height:1.2; color:var(--black); }
    .header-titles p  { font-size:1.5rem; font-weight:400; color:var(--black); margin-top:6px; }

    .h-divider { height:1px; background:var(--divider); width:100%; }

    /* SPLIT */
    .split { display:grid; grid-template-columns:52.15fr 47.85fr; min-height:640px; position:relative; }
    .split::after { content:''; position:absolute; left:52.15%; top:0; bottom:0; width:1px; background:var(--divider); }

    .panel-left { display:flex; align-items:center; justify-content:center; padding:32px 24px 32px 8px; }
    .panel-left img { width:100%; max-width:640px; height:auto; object-fit:cover; }

    .panel-right { padding:36px 64px 48px 56px; display:flex; flex-direction:column; justify-content:flex-start; }
    .form-heading { font-size:1.8rem; font-weight:500; line-height:1.4; color:var(--black); margin-bottom:32px; }

    .field-group { display:flex; flex-direction:column; gap:20px; margin-bottom:24px; }
    .field { display:flex; flex-direction:column; gap:6px; }
    .field label { font-size:1.3rem; font-weight:400; color:var(--black); }
    .field input {
      background:var(--green); border:none; border-radius:11px;
      box-shadow:0 4px 4px rgba(0,0,0,.25); padding:18px 24px;
      font-family:'Inter',sans-serif; font-size:1.25rem; color:#333; outline:none; width:100%;
      transition:background .2s, box-shadow .2s, transform .12s;
    }
    .field input::placeholder { color:var(--gray-ph); }
    .field input:focus { background:#b5ce97; box-shadow:0 4px 14px rgba(80,92,65,.3); transform:translateY(-1px); }

    .btn-cadastrar {
      display:block; width:100%; padding:16px 24px; margin-bottom:20px;
      background:linear-gradient(90deg, var(--green) 60%, var(--green-dk) 100%);
      border:none; border-radius:11px; cursor:pointer;
      font-family:'Inter',sans-serif; font-size:1.25rem; font-weight:600; color:#111;
      box-shadow:0 4px 4px rgba(0,0,0,.2); transition:opacity .15s, transform .12s;
    }
    .btn-cadastrar:hover { opacity:.88; transform:translateY(-1px); }

    .login-prompt { font-size:1.3rem; font-weight:400; color:var(--black); line-height:1.6; }
    .login-prompt a { display:block; font-size:1.3rem; font-weight:800; color:var(--teal); text-decoration:none; transition:opacity .15s; }
    .login-prompt a:hover { opacity:.75; text-decoration:underline; }

    /* PRIVACY */
    .privacy-section { padding:56px 64px 72px; }
    .privacy-box { background:var(--salmon); border-radius:15px; padding:44px 56px 44px; font-size:1.2rem; font-weight:400; line-height:1.75; color:#111; }
    .privacy-box p + p { margin-top:14px; }
    .btn-suporte {
      display:block; margin:32px auto 0; background:var(--gray-btn); border:none; border-radius:20px;
      padding:12px 0; width:280px; font-family:'Inter',sans-serif; font-size:1.5rem; font-weight:800;
      color:var(--teal); cursor:pointer; text-align:center; text-decoration:none;
      transition:background .2s, transform .12s;
    }
    .btn-suporte:hover { background:#c5c5c5; transform:translateY(-2px); }

    @media (max-width:900px) {
      .split { grid-template-columns:1fr; }
      .split::after { display:none; }
      .panel-left { display:none; }
      .panel-right { padding:36px 32px 48px; }
      .site-header { flex-direction:column; text-align:center; padding:24px 24px 16px; }
      .header-titles h1 { font-size:1.7rem; }
      .header-titles p  { font-size:1.1rem; }
      .privacy-section { padding:40px 24px 56px; }
      .privacy-box { padding:28px 28px; font-size:1rem; }
    }
  </style>
</head>
<body>

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
