<?php
session_start();
require 'tmdb.php';
require 'db.php';

if (isset($_GET['set_genre']) && ctype_digit($_GET['set_genre'])) {
  $genreId = (int) $_GET['set_genre'];
  $allowedGenres = [28, 35, 18, 27, 10749];
  if (in_array($genreId, $allowedGenres)) {
    $_SESSION['genre'] = $genreId;
    setcookie('genre', $genreId, time() + 604800, '/', '', false, true);
  }
  header("Location: index.php");
  exit;
}

$genreId = 28;
if (isset($_SESSION['genre']) && ctype_digit(strval($_SESSION['genre']))) {
  $genreId = (int) $_SESSION['genre'];
} elseif (isset($_COOKIE['genre']) && ctype_digit($_COOKIE['genre'])) {
  $genreId = (int) $_COOKIE['genre'];
}

$recomendadas = fetchPopularMovies($genreId, 1);
$recomendadasSe = fetchPopularSeries($genreId, 1);
$peliculas = fetchPopularMovies(null, 1);
$series = fetchPopularSeries(null, 1);

$userId = null;
$valoraciones = [];
$favoritos = ['movie' => [], 'serie' => []];

if (isset($_SESSION['user'])) {
  $stmtUser = $pdo->prepare("SELECT id FROM users WHERE username = ?");
  $stmtUser->execute([$_SESSION['user']]);
  $userId = $stmtUser->fetchColumn();

  if ($userId) {
    $stmtRatings = $pdo->prepare("SELECT movie_id, serie_id, rating FROM ratings WHERE user_id = ?");
    $stmtRatings->execute([$userId]);
    foreach ($stmtRatings->fetchAll() as $r) {
      if ($r['movie_id']) {
        $valoraciones['movie_' . $r['movie_id']] = (int)$r['rating'];
      } elseif ($r['serie_id']) {
        $valoraciones['serie_' . $r['serie_id']] = (int)$r['rating'];
      }
    }

    $stmtFavs = $pdo->prepare("SELECT movie_id, serie_id FROM favorites WHERE user_id = ?");
    $stmtFavs->execute([$userId]);
    foreach ($stmtFavs->fetchAll() as $fav) {
      if ($fav['movie_id']) $favoritos['movie'][] = $fav['movie_id'];
      if ($fav['serie_id']) $favoritos['serie'][] = $fav['serie_id'];
    }
  }
}

$genres = [
  28 => 'Acción',
  35 => 'Comedia', 
  18 => 'Drama',
  27 => 'Terror',
  10749 => 'Romance'
];
$nombreGenero = htmlspecialchars($genres[$genreId] ?? 'Todas', ENT_QUOTES, 'UTF-8');
?><!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>RateMyMovie - Tu Guía de Películas y Series</title>
  <meta name="description" content="Descubre, califica y comparte tus películas y series favoritas en RateMyMovie">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<!-- Hero Section -->
<div class="hero">
  <div class="hero-content">
    <h1>Bienvenido a RateMyMovie</h1>
    <p>Tu destino definitivo para descubrir y calificar el mejor contenido</p>
  </div>
</div>

<div class="swiper main-slider animate__animated animate__slideInUp">
  <h2 class="section-title">🎯 Recomendadas: <?= $nombreGenero ?></h2>
  <div class="swiper-wrapper">
    <?php foreach ($recomendadas as $movie): ?>
      <div class="swiper-slide card-hover">
        <a href="rate.php?id=<?= $movie['id'] ?>">
          <div class="poster-wrapper">
            <img src="https://image.tmdb.org/t/p/w300<?= $movie['poster_path'] ?>" alt="<?= htmlspecialchars($movie['title']) ?>">
            <div class="poster-overlay">
              <span class="view-details">Ver Detalles</span>
            </div>
          </div>
          <p class="movie-title"><?= htmlspecialchars($movie['title']) ?></p>
          <?php if (isset($valoraciones['movie_' . $movie['id']])): ?>
            <div class="star-rating animate__animated animate__fadeIn">
              <?php
              $rating = $valoraciones['movie_' . $movie['id']];
              for ($i = 1; $i <= 5; $i++) echo $i <= $rating ? '★' : '☆';
              ?>
            </div>
          <?php endif; ?>
        </a>
      </div>
    <?php endforeach; ?>
  </div>
  <div class="swiper-pagination"></div>
  <div class="swiper-button-next"></div>
  <div class="swiper-button-prev"></div>
</div>

<section class="content-section animate__animated animate__fadeIn">
  <h3 class="section-title">🎬 Películas Populares</h3>
  <div class="movie-grid">
    <?php foreach ($peliculas as $movie): ?>
      <div class="movie-card card-hover">
        <a href="rate.php?id=<?= $movie['id'] ?>">
          <div class="poster-wrapper">
            <img src="https://image.tmdb.org/t/p/w200<?= $movie['poster_path'] ?>" alt="<?= htmlspecialchars($movie['title']) ?>">
            <div class="poster-overlay">
              <span class="view-details">Ver Detalles</span>
            </div>
          </div>
          <p class="movie-title"><?= htmlspecialchars($movie['title']) ?></p>
          <?php if (isset($valoraciones['movie_' . $movie['id']])): ?>
            <div class="star-rating">
              <?php
              $rating = $valoraciones['movie_' . $movie['id']];
              for ($i = 1; $i <= 5; $i++) echo $i <= $rating ? '★' : '☆';
              ?>
            </div>
          <?php endif; ?>
        </a>
        <?php if ($userId): ?>
          <button class="favorite-btn pulse <?= in_array($movie['id'], $favoritos['movie']) ? 'favorited' : '' ?>" data-movie-id="<?= $movie['id'] ?>">
            <i class="fa fa-heart"></i>
          </button>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<div class="swiper series-slider animate__animated animate__slideInUp">
  <h2 class="section-title">🎯 Series Recomendadas: <?= $nombreGenero ?></h2>
  <div class="swiper-wrapper">
    <?php foreach ($recomendadasSe as $serie): ?>
      <div class="swiper-slide card-hover">
        <a href="rateSerie.php?id=<?= $serie['id'] ?>">
          <div class="poster-wrapper">
            <img src="https://image.tmdb.org/t/p/w300<?= $serie['poster_path'] ?>" alt="<?= htmlspecialchars($serie['name']) ?>">
            <div class="poster-overlay">
              <span class="view-details">Ver Detalles</span>
            </div>
          </div>
          <p class="serie-title"><?= htmlspecialchars($serie['name']) ?></p>
          <?php if (isset($valoraciones['serie_' . $serie['id']])): ?>
            <div class="star-rating animate__animated animate__fadeIn">
              <?php
              $rating = $valoraciones['serie_' . $serie['id']];
              for ($i = 1; $i <= 5; $i++) echo $i <= $rating ? '★' : '☆';
              ?>
            </div>
          <?php endif; ?>
        </a>
      </div>
    <?php endforeach; ?>
  </div>
  <div class="swiper-pagination"></div>
  <div class="swiper-button-next"></div>
  <div class="swiper-button-prev"></div>
</div>

<section class="content-section animate__animated animate__fadeIn">
  <h3 class="section-title">📺 Series Populares</h3>
  <div class="movie-grid">
    <?php foreach ($series as $serie): ?>
      <div class="movie-card card-hover">
        <a href="rateSerie.php?id=<?= $serie['id'] ?>">
          <div class="poster-wrapper">
            <img src="https://image.tmdb.org/t/p/w200<?= $serie['poster_path'] ?>" alt="<?= htmlspecialchars($serie['name']) ?>">
            <div class="poster-overlay">
              <span class="view-details">Ver Detalles</span>
            </div>
          </div>
          <p class="serie-title"><?= htmlspecialchars($serie['name']) ?></p>
          <?php if (isset($valoraciones['serie_' . $serie['id']])): ?>
            <div class="star-rating">
              <?php
              $rating = $valoraciones['serie_' . $serie['id']];
              for ($i = 1; $i <= 5; $i++) echo $i <= $rating ? '★' : '☆';
              ?>
            </div>
          <?php endif; ?>
        </a>
        <?php if ($userId): ?>
          <button class="favorite-btn pulse <?= in_array($serie['id'], $favoritos['serie']) ? 'favorited' : '' ?>" data-serie-id="<?= $serie['id'] ?>">
            <i class="fa fa-heart"></i>
          </button>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<script>
document.querySelectorAll('.favorite-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        this.classList.add('animate__animated', 'animate__heartBeat');
        const formData = new FormData();
        const movieId = this.dataset.movieId;
        const serieId = this.dataset.serieId;

        if (movieId) formData.append('movie_id', movieId);
        if (serieId) formData.append('serie_id', serieId);

        fetch('toggle_favorite.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'added') {
                this.classList.add('favorited');
            } else if (data.status === 'removed') {
                this.classList.remove('favorited');
            }
        })
        .catch(err => console.error('Error:', err));
    });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
<script>
  new Swiper('.swiper', {
    slidesPerView: 1,
    spaceBetween: 30,
    loop: true,
    effect: 'coverflow',
    centeredSlides: true,
    autoplay: {
      delay: 3000,
      disableOnInteraction: false,
    },
    pagination: {
      el: '.swiper-pagination',
      clickable: true,
    },
    navigation: {
      nextEl: '.swiper-button-next',
      prevEl: '.swiper-button-prev',
    },
    breakpoints: {
      640: {
        slidesPerView: 2,
      },
      768: {
        slidesPerView: 3,
      },
    }
  });

  function toggleTheme() {
    const isDark = document.body.classList.contains('dark');
    const next = isDark ? 'light' : 'dark';
    document.body.classList.remove('light', 'dark');
    document.body.classList.add(next);
    document.cookie = "theme=" + next + "; path=/; max-age=31536000";
    document.getElementById('themeToggle').checked = next === 'dark';
  }

  function applyThemeFromCookie() {
    const match = document.cookie.match(/theme=(light|dark)/);
    const theme = match ? match[1] : 'light';
    document.body.classList.add(theme);
    if (theme === 'dark') {
      document.getElementById('themeToggle').checked = true;
    }
  }
  applyThemeFromCookie();
</script>

<script>
  function toggleMenu() {
    const menu = document.querySelector('.ul');
    menu.classList.toggle('show');
  }

  // Add smooth scroll reveal
  window.addEventListener('scroll', function() {
    document.querySelectorAll('.content-section').forEach(section => {
      const rect = section.getBoundingClientRect();
      if (rect.top < window.innerHeight - 100) {
        section.classList.add('animate__fadeIn');
        section.style.opacity = '1';
      }
    });
  });
</script>

<footer class="main-footer animate__animated animate__fadeIn">
  <div class="footer-container">
    <div class="footer-logo">
      <img src="css/logo2.png" alt="RateMyMovie Logo" class="animate__animated animate__pulse animate__infinite"/>
      <h3>RateMyMovie</h3>
    </div>
    <div class="footer-social">
      <p>© <?= date('Y') ?> RateMyMovie. Todos los derechos reservados.</p>
      <div class="social-icons">
        <a href="https://facebook.com" target="_blank" aria-label="Facebook" class="social-icon"><i class="fab fa-facebook-f"></i></a>
        <a href="https://twitter.com" target="_blank" aria-label="Twitter" class="social-icon"><i class="fab fa-x-twitter"></i></a>
        <a href="https://instagram.com" target="_blank" aria-label="Instagram" class="social-icon"><i class="fab fa-instagram"></i></a>
      </div>
    </div>
  </div>
</footer>
</body>
</html>