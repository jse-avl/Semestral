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
$stmtUser = $pdo->prepare("SELECT id FROM users WHERE username = ?");
$stmtUser->execute([$_SESSION['user']]);
$userId = $stmtUser->fetchColumn();

if (!$userId) {
    header('Location: login.php');
    exit;
}

// Obtener detalles desde la API TMDB
$movieDetails = getMovieById($movieId);
$title    = $movieDetails['title']        ?? 'Título no disponible';
$poster   = $movieDetails['poster_path']  ?? '';
$release  = $movieDetails['release_date'] ?? 'Desconocido';
$overview = $movieDetails['overview']     ?? 'Sin sinopsis disponible';

// Verificar si el usuario ya valoró esta película
$stmtCheck = $pdo->prepare("SELECT rating, comment FROM ratings WHERE movie_id = ? AND user_id = ?");
$stmtCheck->execute([$movieId, $userId]);
$myRating = $stmtCheck->fetch();

// Verificar si ya es favorita
$stmtFav = $pdo->prepare("SELECT COUNT(*) FROM favorites WHERE user_id = ? AND movie_id = ?");
$stmtFav->execute([$userId, $movieId]);
$isFavorite = $stmtFav->fetchColumn() > 0;

// Procesar nueva valoración
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
        <button id="favoriteBtn" class="fav-btn" onclick="toggleFavorite(<?= $movieId ?>)">
          <?= $isFavorite ? '💔 Quitar de Favoritos' : '❤️ Agregar a Favoritos' ?>
        </button>
      </div>
    </div>

    <?php if ($myRating): ?>
      <p><strong>Tu valoración:</strong> ⭐ <?= $myRating['rating'] ?>/5</p>
      <p><strong>Tu comentario:</strong> <?= $myRating['comment'] ?: 'Sin comentario' ?></p>
    <?php endif; ?>
  </div>

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
      <?php endforeach; ?>
    <?php else: ?>
      <p>No hay comentarios disponibles para esta película.</p>
    <?php endif; ?>
  </div>

  <script>
    function toggleTheme() {
      const current = document.body.classList.contains('dark') ? 'dark' : 'light';
      const next = current === 'dark' ? 'light' : 'dark';
      document.body.classList.remove(current);
      document.body.classList.add(next);
      document.cookie = "theme=" + next + "; path=/; max-age=31536000";
    }

    function applyThemeFromCookie() {
      const match = document.cookie.match(/theme=(light|dark)/);
      const theme = match ? match[1] : 'light';
      document.body.classList.add(theme);
    }

    applyThemeFromCookie();

    function toggleFavorite(movieId) {
      fetch('toggle_favorite.php?id=' + movieId)
        .then(res => res.json())
        .then(data => {
          const btn = document.getElementById('favoriteBtn');
          if (data.status === 'added') {
            btn.textContent = '💔 Quitar de Favoritos';
          } else if (data.status === 'removed') {
            btn.textContent = '❤️ Agregar a Favoritos';
          }
        });
    }
  </script>
</body>
</html>