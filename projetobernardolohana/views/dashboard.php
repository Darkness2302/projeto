<?php
$nome   = $_SESSION['nome']   ?? 'Usuário';
$perfil = $_SESSION['perfil'] ?? '';

function imagemProdutoDashboard(int $produtoId): string
{
    $baseFs  = __DIR__ . "/../public/uploads/produtos/";
    $baseUrl = "public/uploads/produtos/";
    foreach (['jpg', 'png', 'webp'] as $ext) {
        if (file_exists($baseFs . $produtoId . '.' . $ext)) {
            return $baseUrl . $produtoId . '.' . $ext;
        }
    }
    return "public/assets/img/produto_sem_foto.png";
}

// Legenda do banner: usa a frase "de sempre" quando o nome da categoria bate com o texto
// original do Figma; para categorias novas que o gerente criar, cai num texto genérico.
function bannerCategoria(string $nome): string
{
    $mapa = [
        'Refeições'  => 'Refeições mais populares do nosso restaurante.',
        'Bebidas'    => 'Bebidas mais pedidas.',
        'Sobremesas' => 'Sobremesas famosas.',
    ];
    return $mapa[$nome] ?? ($nome . ' disponíveis.');
}

// $categoriasProdutos vem do AuthController::dashboard() — só categorias com
// pelo menos 1 produto ativo, na mesma ordem usada no Estoque.
$categoriasProdutos = $categoriasProdutos ?? [];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Panela Quente – Menu</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="public/assets/css/style.css">
</head>
<body data-page="dashboard">

  <header>
    <div class="page-header">
      <div class="logo-wrap">
        <img src="public/uploads/img/Cópia de R.P.Q. Logo (2) 4.png"
             alt="Restaurante Panela Quente" />
      </div>
      <div class="header-center">
        <h1 class="header-title">Bem-vindo <?= htmlspecialchars($nome) ?> ao Panela Quente</h1>
        <a class="header-logout" href="index.php?controller=auth&action=logout">
          (<?= htmlspecialchars($perfil) ?>) &mdash; Sair
        </a>
      </div>
    </div>
    <nav class="nav-bar">
      <?php if (acl_podeVer('dashboard')): ?>
        <a href="index.php?controller=auth&action=dashboard" class="active">Menu</a>
      <?php endif; ?>
      <?php if (acl_podeVer('fornecedor')): ?>
        <a href="index.php?controller=fornecedor&action=index">Fornecedores</a>
      <?php endif; ?>
      <?php if (acl_podeVer('produto')): ?>
        <a href="index.php?controller=produto&action=index">Estoque</a>
      <?php endif; ?>
      <?php if (acl_podeVer('venda')): ?>
        <a href="index.php?controller=venda&action=index">Pedidos</a>
      <?php endif; ?>
    </nav>
  </header>

  <div class="hero">
    <img class="hero-img" src="public/uploads/img/60 4 1.png" alt="Prato" />
    <img class="hero-img" src="public/uploads/img/60 1 1.png" alt="Prato" />
    <img class="hero-img" src="public/uploads/img/60 3 1.png" alt="Prato" />
    <img class="hero-img" src="public/uploads/img/60 2 1.png" alt="Prato" />
  </div>

  <?php if (empty($categoriasProdutos)): ?>
    <div class="empty-cardapio">Nenhum produto disponível no momento. Volte em breve!</div>
  <?php else: ?>
    <?php foreach ($categoriasProdutos as $catId => $cat): ?>
      <div class="section-title-wrap">
        <span class="section-banner"><?= htmlspecialchars(bannerCategoria($cat['nome'])) ?></span>
      </div>
      <div class="carousel-outer" data-carousel>
        <button type="button" class="car-arrow car-prev" aria-label="Produto anterior">&#8592;</button>
        <div class="carousel-viewport">
          <div class="carousel-track">
            <?php foreach ($cat['produtos'] as $p): ?>
              <div class="product-card">
                <img class="product-card__img"
                     src="<?= htmlspecialchars(imagemProdutoDashboard((int)$p['id'])) ?>"
                     alt="<?= htmlspecialchars($p['nome']) ?>" loading="lazy" decoding="async" />
                <span class="product-card__label"><?= htmlspecialchars($p['nome']) ?></span>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
        <button type="button" class="car-arrow car-next" aria-label="Próximo produto">&#8594;</button>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>

  <footer class="footer">
    <div class="footer__about">
      <h3>Sobre nosso restaurante:</h3>
      <div class="footer__about-box">
        <p>&nbsp;&nbsp;&nbsp;Somos um restaurante de comida caseira, oferecemos preços acessíveis, ambiente confortável e climatizado, além de contribuirmos para instituições que levam a nutrição e ajudam a sanar a fome de pessoas necessitadas.</p>
        <p>&nbsp;&nbsp;&nbsp;Nosso prazer é levar o melhor da culinária local até sua mesa, fazendo seus almoços em família, e pausas do trabalho, memoráveis e acolhendo ideias que buscam a melhora de nosso estabelecimento e nossos serviços.</p>
        <p>&nbsp;&nbsp;&nbsp;Temos orgulho em servir à você com o melhor da Panela Quente.</p>
      </div>
    </div>
    <div class="footer__contact">
      <h3>Nossos contatos:</h3>
      <div class="contact-list">
        <div class="contact-item">
          <img src="public/uploads/img/image 14.png" alt="WhatsApp" />
          <span>(21) 99010-5501</span>
        </div>
        <div class="contact-item">
          <img src="public/uploads/img/image 13.png" alt="Instagram" />
          <span>@RestaurantePanelaQuente_oficial</span>
        </div>
        <div class="contact-item">
          <img src="public/uploads/img/image 15.png" alt="YouTube" />
          <span>Canal Receitas Práticas do Panela Quente.</span>
        </div>
        <div class="contact-item">
          <img src="public/uploads/img/image 16 (1).png" alt="Facebook" />
          <span>@RestaurantePanelaQuente_oficial</span>
        </div>
      </div>
      <div class="footer-logo-wrap">
        <img src="public/uploads/img/Cópia de R.P.Q. Logo (2) 4.png" alt="Restaurante Panela Quente" />
      </div>
    </div>
  </footer>

  <script>
    // Setas: avançam/voltam um cartão por clique (a largura do cartão é medida
    // em tempo real, então funciona igual em qualquer tamanho de tela).
    document.querySelectorAll('[data-carousel]').forEach(function (carousel) {
      const viewport = carousel.querySelector('.carousel-viewport');
      const track    = carousel.querySelector('.carousel-track');
      const prevBtn  = carousel.querySelector('.car-prev');
      const nextBtn  = carousel.querySelector('.car-next');
      const cards    = Array.from(track.children);
      if (cards.length === 0) return;

      function passo() {
        const style = getComputedStyle(track);
        const gap = parseFloat(style.columnGap || style.gap || '0');
        return cards[0].getBoundingClientRect().width + gap;
      }
      function atualizarBotoes() {
        const max = viewport.scrollWidth - viewport.clientWidth - 1;
        prevBtn.disabled = viewport.scrollLeft <= 0;
        nextBtn.disabled = viewport.scrollLeft >= max || max <= 0;
      }
      prevBtn.addEventListener('click', () => { viewport.scrollLeft -= passo(); });
      nextBtn.addEventListener('click', () => { viewport.scrollLeft += passo(); });
      viewport.addEventListener('scroll', atualizarBotoes, { passive: true });
      window.addEventListener('resize', atualizarBotoes);
      atualizarBotoes();
    });
  </script>

</body>
</html>
