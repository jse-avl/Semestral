<?php
session_start();
require 'db.php';
require 'tmdb.php';

if (!isset($_SESSION['user'])) {
  header("Location: login.php?error=auth_required");
  exit;
}

// Validar que la sesión tenga un usuario válido
$stmtUser = $pdo->prepare("SELECT id FROM users WHERE username = ?");
$stmtUser->execute([$_SESSION['user']]);
$userId = $stmtUser->fetchColumn();

if (!$userId) {
  header("Location: login.php?error=invalid_user");
  exit;
}

// Solo procesar eliminación si la entrada es válida y POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_movie_id'])) {
  $removeMovieId = filter_var($_POST['remove_movie_id'], FILTER_VALIDATE_INT);
  if ($removeMovieId) {
    $stmtDel = $pdo->prepare("DELETE FROM favorites WHERE user_id = ? AND movie_id = ?");
    $stmtDel->execute([$userId, $removeMovieId]);
  }
  header("Location: " . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'));
  exit;
}

// Obtener películas favoritas
$stmtFav = $pdo->prepare("SELECT movie_id FROM favorites WHERE user_id = ?");
$stmtFav->execute([$userId]);
$favMovies = $stmtFav->fetchAll(PDO::FETCH_COLUMN);

$favoritos = [];
foreach ($favMovies as $movieId) {
  $movie = getMovieById($movieId);
  if (is_array($movie) && isset($movie['id'])) {
    $favoritos[] = [
      'id' => $movie['id'],
      'title' => htmlspecialchars($movie['title'] ?? 'Sin título', ENT_QUOTES, 'UTF-8'),
      'poster_path' => $movie['poster_path'] ?? '',
    ];
  }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Mis Películas Favoritas</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <?php include 'includes/navbar.php'; ?>

  <h2>🎥 Mis Películas Favoritas</h2>

  <?php if (empty($favoritos)): ?>
    <p style="margin: 20px;">Aún no has agregado películas a tus favoritos.</p>
  <?php else: ?>
    <div class="movie-grid">
      <?php foreach ($favoritos as $movie): ?>
        <div class="movie-card">
          <a href="rate.php?id=<?= $movie['id'] ?>">
            <?php if ($movie['poster_path']): ?>
              <img src="https://image.tmdb.org/t/p/w200<?= htmlspecialchars($movie['poster_path'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= $movie['title'] ?>">
            <?php else: ?>
              <p>[Sin imagen]</p>
            <?php endif; ?>
            <p><?= $movie['title'] ?></p>
          </a>
          <form method="POST" style="text-align:center;">
            <input type="hidden" name="remove_movie_id" value="<?= $movie['id'] ?>">
            <button type="submit" class="fav-btn">💔 Quitar</button>
          </form>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

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
  </script>
</body>
</html>