<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php?error=auth_required");
    exit;
}

require 'tmdb.php';

$results = [];
$query = '';

// Validar entrada y evitar manipulación
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['q']) && strlen(trim($_GET['q'])) > 0) {
    $queryRaw = trim($_GET['q']);

    $query = htmlspecialchars($queryRaw, ENT_QUOTES, 'UTF-8');

    // Límite de longitud (máximo 100 caracteres)
    if (strlen($queryRaw) > 100) {
        $query = substr($queryRaw, 0, 100);
    }

    // Buscar película
    $results = searchMovies($queryRaw);

    // Si la API falla, evitar errores
    if (!is_array($results)) {
        $results = [];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Buscar Películas</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <?php include 'includes/navbar.php'; ?>

  <h2>Buscar Películas</h2>

  <form method="GET" class="buscar-form">
    <input class="flip-card__input" type="text" name="q" placeholder="Ej: Batman, Matrix..." value="<?= $query ?>" maxlength="100" required>
    <button class="flip-card__btn" type="submit">Buscar</button>
  </form>

  <?php if ($query && empty($results)): ?>
    <p class="p">No se encontraron resultados para <strong><?= $query ?></strong>.</p>
  <?php endif; ?>

  <div class="movie-grid">
  <?php foreach ($results as $movie): ?>
    <?php
      $movieId = (int) ($movie['id'] ?? 0);
      $title   = htmlspecialchars($movie['title'] ?? 'Sin título', ENT_QUOTES, 'UTF-8');
      $poster  = $movie['poster_path'] ?? '';
    ?>
    <a class="movie-card" href="rate.php?id=<?= $movieId ?>" title="Ver detalles">
      <?php if ($poster): ?>
        <img src="https://image.tmdb.org/t/p/w200<?= htmlspecialchars($poster, ENT_QUOTES, 'UTF-8') ?>" alt="<?= $title ?>">
      <?php else: ?>
        <p>[Sin imagen disponible]</p>
      <?php endif; ?>
      <h3><?= $title ?></h3>
    </a>
  <?php endforeach; ?>
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
  </script>
</body>
</html>