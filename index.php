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
  <title>Películas y Series Recomendadas</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="swiper">
  <h2>🎯 Recomendadas: <?= $nombreGenero ?></h2>
  <div class="swiper-wrapper">
    <?php foreach ($recomendadas as $movie): ?>
      <div class="swiper-slide">
        <a href="rate.php?id=<?= $movie['id'] ?>">
          <img src="https://image.tmdb.org/t/p/w300<?= $movie['poster_path'] ?>" alt="<?= htmlspecialchars($movie['title']) ?>">
          <p><?= htmlspecialchars($movie['title']) ?></p>
          <?php if (isset($valoraciones['movie_' . $movie['id']])): ?>
            <div class="star-rating">
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
</div>

<h3>🎬 Películas</h3>
<div class="movie-grid">
  <?php foreach ($peliculas as $movie): ?>
    <div class="movie-card">
      <a href="rate.php?id=<?= $movie['id'] ?>">
        <img src="https://image.tmdb.org/t/p/w200<?= $movie['poster_path'] ?>" alt="<?= htmlspecialchars($movie['title']) ?>">
        <p><?= htmlspecialchars($movie['title']) ?></p>
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
        <button class="favorite-btn <?= in_array($movie['id'], $favoritos['movie']) ? 'favorited' : '' ?>" data-movie-id="<?= $movie['id'] ?>">
          <i class="fa fa-heart"></i>
        </button>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
</div>

<div class="swiper">
  <h2>🎯 Series Recomendadas: <?= $nombreGenero ?></h2>
  <div class="swiper-wrapper">
    <?php foreach ($recomendadasSe as $serie): ?>
      <div class="swiper-slide">
        <a href="rateSerie.php?id=<?= $serie['id'] ?>">
          <img src="https://image.tmdb.org/t/p/w300<?= $serie['poster_path'] ?>" alt="<?= htmlspecialchars($serie['name']) ?>">
          <p><?= htmlspecialchars($serie['name']) ?></p>
          <?php if (isset($valoraciones['serie_' . $serie['id']])): ?>
            <div class="star-rating">
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
</div>

<h3>📺 Series</h3>
<div class="movie-grid">
  <?php foreach ($series as $serie): ?>
    <div class="movie-card">
      <a href="rateSerie.php?id=<?= $serie['id'] ?>">
        <img src="https://image.tmdb.org/t/p/w200<?= $serie['poster_path'] ?>" alt="<?= htmlspecialchars($serie['name']) ?>">
        <p><?= htmlspecialchars($serie['name']) ?></p>
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
        <button class="favorite-btn <?= in_array($serie['id'], $favoritos['serie']) ? 'favorited' : '' ?>" data-serie-id="<?= $serie['id'] ?>">
          <i class="fa fa-heart"></i>
        </button>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
</div>

<script>
document.querySelectorAll('.favorite-btn').forEach(btn => {
    btn.addEventListener('click', function () {
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

<!-- Swiper + Temas -->
<script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
<script>
  new Swiper('.swiper', {
    slidesPerView: 3,
    spaceBetween: 10,
    loop: true,
    pagination: { el: '.swiper-pagination' },
    autoplay: { delay: 3000 }
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
</script>

<!-- Footer -->
<footer class="main-footer">
  <div class="footer-container">
    <div class="footer-logo">
      <img src="css/logo2.png" alt="Logo" />
      <h3>RateMyMovie</h3>
    </div>
    <div class="footer-social">
      <p>© <?= date('Y') ?> RateMyMovie. Todos los derechos reservados.</p>
      <div class="social-icons">
        <a href="https://facebook.com" target="_blank" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
        <a href="https://twitter.com" target="_blank" aria-label="Twitter"><i class="fab fa-x-twitter"></i></a>
        <a href="https://instagram.com" target="_blank" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
      </div>
    </div>
  </div>
</footer>