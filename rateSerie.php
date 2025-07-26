<?php
session_start();
require 'db.php';
require 'tmdb.php';

$username = $_SESSION['user'] ?? null;
$role = $_SESSION['role'] ?? null;
$userId = null;

if (!$username || !$role) {
    header('Location: login.php');
    exit;
}

// Obtener ID del usuario normal
if ($role === 'user') {
    $stmtUser = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmtUser->execute([$username]);
    $userId = $stmtUser->fetchColumn();

    if (!$userId) {
        header('Location: login.php');
        exit;
    }
}

$serieId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($serieId <= 0) {
    die("Serie no especificada.");
}

// Obtener detalles desde la API TMDB
$serieDetails = getSerieById($serieId);

$title    = $serieDetails['name'] ?? 'Título no disponible';
$poster   = $serieDetails['poster_path'] ?? '';
$release  = $serieDetails['first_air_date'] ?? 'Desconocido';
$overview = $serieDetails['overview'] ?? 'Sin sinopsis disponible';

// Tráiler de YouTube
$trailerKey = null;
if (!empty($serieDetails['videos']['results'])) {
    foreach ($serieDetails['videos']['results'] as $video) {
        if ($video['site'] === 'YouTube' && $video['type'] === 'Trailer') {
            $trailerKey = $video['key'];
            break;
        }
    }
}

// Obtener valoración y favoritos solo si es usuario normal
$myRating = null;
$isFavorite = false;

if ($role === 'user' && $userId) {
    $stmtCheck = $pdo->prepare("SELECT rating, comment FROM ratings WHERE serie_id = ? AND user_id = ?");
    $stmtCheck->execute([$serieId, $userId]);
    $myRating = $stmtCheck->fetch();

    $stmtFav = $pdo->prepare("SELECT COUNT(*) FROM favorites WHERE user_id = ? AND serie_id = ?");
    $stmtFav->execute([$userId, $serieId]);
    $isFavorite = $stmtFav->fetchColumn() > 0;
}

// Procesar formulario POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $role === 'user' && $userId) {
    $rating = (int) ($_POST['rating'] ?? 0);
    $comment = htmlspecialchars(trim($_POST['comment'] ?? ''), ENT_QUOTES, 'UTF-8');

    if ($rating >= 1 && $rating <= 5) {
        if ($myRating) {
            $stmt = $pdo->prepare("UPDATE ratings SET rating = ?, comment = ? WHERE serie_id = ? AND user_id = ?");
            $stmt->execute([$rating, $comment, $serieId, $userId]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO ratings (serie_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
            $stmt->execute([$serieId, $userId, $rating, $comment]);
        }

        include 'update_genre_cookie.php';
        header("Location: rateSerie.php?id=$serieId");
        exit;
    }
}

// Obtener comentarios vía SOAP
try {
    $soapClient = new SoapClient("http://localhost/Semestral/service.wsdl", ['cache_wsdl' => WSDL_CACHE_NONE]);
    $response = $soapClient->__soapCall("getMovieRatings", [["serieId" => $serieId]]);
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
  <button id="favoriteBtn" class="fav-btn" onclick="toggleFavorite(<?= $serieId ?>)">
    <?= $isFavorite ? '💔 Quitar de Favoritos' : '❤️ Agregar a Favoritos' ?>
  </button>
<?php endif; ?>
      </div>
    </div>

    <?php if ($trailerKey): ?>
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
  <!-- formulario de calificación -->
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
    <textarea name="comment" placeholder="¿Qué opinas de esta serie?"><?= htmlspecialchars($myRating['comment'] ?? '') ?></textarea>

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

    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
      <form method="POST" action="/Semestral/admin/delete_comment_series.php" class="delete-comment-form">
        <input type="hidden" name="comment_id" value="<?= $c['id'] ?>">
        <input type="hidden" name="serie_id" value="<?= $serieId ?>">
        <button type="submit" onclick="return confirm('¿Eliminar este comentario?')">🗑️ Eliminar</button>
      </form>
    <?php endif; ?>
  </div>
<?php endforeach; ?>

    <?php else: ?>
      <p>No hay comentarios disponibles para esta serie.</p>
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

    function toggleFavorite(serieId) {
      fetch('toggle_favorite.php?id=' + serieId)
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