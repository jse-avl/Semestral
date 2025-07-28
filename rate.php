<?php
session_start();
require 'db.php';
require 'tmdb.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$movieId = (int) ($_GET['id'] ?? 0);
if (!$movieId) {
    die("Película no especificada.");
}

// Obtener ID del usuario
$role = $_SESSION['role'] ?? '';
$userId = null;

if ($role === 'user') {
    $stmtUser = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmtUser->execute([$_SESSION['user']]);
    $userId = $stmtUser->fetchColumn();

    if (!$userId) {
        header('Location: login.php');
        exit;
    }
}

// Obtener detalles desde la API TMDB (incluyendo trailers)
$movieDetails = getMovieById($movieId); // 🆕 asegúrate que append_to_response=videos en tmdb.php

$title    = $movieDetails['title']        ?? 'Título no disponible';
$poster   = $movieDetails['poster_path']  ?? '';
$release  = $movieDetails['release_date'] ?? 'Desconocido';
$overview = $movieDetails['overview']     ?? 'Sin sinopsis disponible';

// 🆕 Obtener el tráiler de YouTube
$trailerKey = null;
if (!empty($movieDetails['videos']['results'])) {
    foreach ($movieDetails['videos']['results'] as $video) {
        if ($video['site'] === 'YouTube' && $video['type'] === 'Trailer') {
            $trailerKey = $video['key'];
            break;
        }
    }
}

// Verificar si el usuario ya valoró esta película
$stmtCheck = $pdo->prepare("SELECT rating, comment FROM ratings WHERE movie_id = ? AND user_id = ?");
$stmtCheck->execute([$movieId, $userId]);
$myRating = $stmtCheck->fetch();

// Verificar si ya es favorita
 $stmtFavs = $pdo->prepare("SELECT movie_id, serie_id FROM favorites WHERE user_id = ?");
    $stmtFavs->execute([$userId]);
    foreach ($stmtFavs->fetchAll() as $fav) {
      if ($fav['movie_id']) $favoritos['movie'][] = $fav['movie_id'];
     
    }
// Procesar nueva valoración
$rating = isset($_POST['rating']) ? (int) $_POST['rating'] : 0;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating = (int) $_POST['rating'];
    $comment = htmlspecialchars(trim($_POST['comment']), ENT_QUOTES, 'UTF-8');

    if ($rating >= 1 && $rating <= 5) {
        if ($myRating) {
            $stmt = $pdo->prepare("UPDATE ratings SET rating = ?, comment = ? WHERE movie_id = ? AND user_id = ?");
            $stmt->execute([$rating, $comment, $movieId, $userId]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO ratings (movie_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
            $stmt->execute([$movieId, $userId, $rating, $comment]);
        }

        include 'update_genre_cookie.php';
        header("Location: rate.php?id=$movieId");
        exit;
    }
}

// Obtener comentarios de otros usuarios usando SOAP
try {
    $soapClient = new SoapClient("http://localhost/Semestral/service.wsdl", [
        'cache_wsdl' => WSDL_CACHE_NONE
    ]);
    $response = $soapClient->__soapCall("getMovieRatings", [["movieId" => $movieId]]);
    $comentarios = json_decode($response, true);
} catch (SoapFault $e) {
    $comentarios = [];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($title) ?></title>
  <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>
<body>
  <?php include 'includes/navbar.php'; ?>

  <div class="movie-detail">
    <h2 class="movie-title"><?= htmlspecialchars($title) ?></h2>
    <div class="movie-info">
      <img src="https://image.tmdb.org/t/p/w300<?= $poster ?>" alt="<?= htmlspecialchars($title) ?>">
      <div class="movie-text">
        <p><strong>Fecha de estreno:</strong> <?= $release ?></p>
        <p><strong>Sinopsis:</strong> <?= htmlspecialchars($overview) ?></p>
        <?php if ($role === 'user' && $userId): ?>
         <button class="favorite-btn <?= in_array($movie['id'], $favoritos['movie']) ? 'favorited' : '' ?>" data-movie-id="<?= $movie['id'] ?>">
          <i class="fa fa-heart"></i>
        </button>
        <?php endif; ?>
      </div>
    </div>

    <?php if ($trailerKey): ?> <!-- 🆕 Tráiler -->
      <div class="trailer-container">
        <iframe width="560" height="315"
          src="https://www.youtube.com/embed/<?= htmlspecialchars($trailerKey) ?>"
          title="Tráiler de <?= htmlspecialchars($title) ?>" frameborder="0"
          allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
          allowfullscreen></iframe>
      </div>
    <?php endif; ?>

    <?php if ($role === 'user' && $myRating): ?>
  <p><strong>Tu valoración:</strong> ⭐ <?= $myRating['rating'] ?>/5</p>
  <p><strong>Tu comentario:</strong> <?= $myRating['comment'] ?: 'Sin comentario' ?></p>
<?php endif; ?>
  </div>

  <?php if ($role === 'user'): ?>
  <form method="POST" class="rating-form">
    <h3><?= $myRating ? 'Actualizar' : 'Enviar' ?> tu valoración</h3>

    <label>Calificación:</label>
    <div class="star-select">
      <?php for ($i = 5; $i >= 1; $i--): ?>
        <input type="radio" id="star<?= $i ?>" name="rating" value="<?= $i ?>" <?= (isset($myRating['rating']) && $myRating['rating'] == $i) ? 'checked' : '' ?>>
        <label for="star<?= $i ?>">★</label>
      <?php endfor; ?>
    </div>

    <label>Comentario (opcional):</label>
    <textarea name="comment" placeholder="¿Qué opinas de esta película?"><?= htmlspecialchars($myRating['comment'] ?? '') ?></textarea>

    <button type="submit">Guardar Valoración</button>
  </form>
  <?php endif; ?>


  <div class="comment-section">
    <h3>🗣️ Opiniones de otros usuarios</h3>
    <?php if (is_array($comentarios) && count($comentarios)): ?>
      <?php
        $suma = 0;
        foreach ($comentarios as $c) {
          $suma += (int)$c['rating'];
        }
        $promedio = round($suma / count($comentarios), 1);
      ?>
      <p><strong>Promedio de calificaciones:</strong> ⭐ <?= $promedio ?>/5 basado en <?= count($comentarios) ?> opiniones</p>

      <?php foreach ($comentarios as $c): ?>
        <div class="user-comment">
          <p><strong><?= htmlspecialchars($c['username']) ?>:</strong> ⭐ <?= $c['rating'] ?>/5</p>
          <p><?= $c['comment'] ? htmlspecialchars($c['comment']) : 'Sin comentario' ?></p>
        </div>
        <?php if ($role === 'admin'): ?>
  <form method="POST" action="/Semestral/admin/delete_comment.php" class="delete-comment-form">
  <input type="hidden" name="comment_id" value="<?= $c['id'] ?>">
  <input type="hidden" name="movie_id" value="<?= $movieId ?>">
  <button  class="flip-card__btn" type="submit" onclick="return confirm('¿Eliminar este comentario?')">🗑️ Eliminar</button>
</form>
<?php endif; ?>
      <?php endforeach; ?>
    <?php else: ?>
      <p>No hay comentarios disponibles para esta película.</p>
    <?php endif; ?>
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
</body>
</html>
