<?php $perfil = $_SESSION['perfil'] ?? '—'; ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Panela Quente – Acesso negado</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="public/assets/css/style.css">
</head>
<body data-page="acesso-negado">
  <div class="box">
    <img src="public/uploads/img/Cópia de R.P.Q. Logo (2) 4.png" alt="Panela Quente">
    <h1>Acesso não disponível para o seu perfil</h1>
    <p>Seu perfil atual (<strong><?= htmlspecialchars($perfil) ?></strong>) não tem acesso a esta página.</p>
    <a href="index.php?controller=auth&action=dashboard">Voltar ao início</a>
  </div>
</body>
</html>
