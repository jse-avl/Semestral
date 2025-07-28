<?php
session_start();
require 'db.php';
require 'tmdb.php'; 

if (!isset($_SESSION['user'])) {
  header("Location: login.php?error=auth_required");
  exit;
}

// Verificar usuario válido
$stmtUser = $pdo->prepare("SELECT id FROM users WHERE username = ?");
$stmtUser->execute([$_SESSION['user']]);
$userId = $stmtUser->fetchColumn();

if (!$userId) {
  header("Location: login.php?error=invalid_user");
  exit;
}

// Obtener favoritos (películas y series)
$stmtFav = $pdo->prepare("SELECT movie_id, serie_id FROM favorites WHERE user_id = ?");
$stmtFav->execute([$userId]);
$favs = $stmtFav->fetchAll(PDO::FETCH_ASSOC);

$favoritos = [];

foreach ($favs as $fav) {
  if ($fav['movie_id']) {
    $movie = getMovieById($fav['movie_id']);
    if (is_array($movie) && isset($movie['id'])) {
      $favoritos[] = [
        'id' => $movie['id'],
        'title' => htmlspecialchars($movie['title'] ?? 'Sin título', ENT_QUOTES, 'UTF-8'),
        'poster_path' => $movie['poster_path'] ?? '',
        'type' => 'movie'
      ];
    }
  } elseif ($fav['serie_id']) {
    $serie = getSerieById($fav['serie_id']);
    if (is_array($serie) && isset($serie['id'])) {
      $favoritos[] = [
        'id' => $serie['id'],
        'title' => htmlspecialchars($serie['name'] ?? 'Sin título', ENT_QUOTES, 'UTF-8'),
        'poster_path' => $serie['poster_path'] ?? '',
        'type' => 'tv'  // Changed from 'serie' to 'tv' to match TMDB naming
      ];
    }
  }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Mis Favoritos</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <style>
    .fav-remove-btn {
      background: none;
      border: none;
      color: #e74c3c;
      cursor: pointer;
      font-size: 1rem;
      padding: 6px;
      transition: transform 0.2s;
    }

    .fav-remove-btn:hover {
      transform: scale(1.2);
    }
  </style>
</head>
<body>
  <?php include 'includes/navbar.php'; ?>

  <h2>🌟 Mis Favoritos (Películas y Series)</h2>

  <?php if (empty($favoritos)): ?>
    <p style="margin: 20px;">Aún no has agregado contenido a tus favoritos.</p>
  <?php else: ?>
    <div class="movie-grid">
      <?php foreach ($favoritos as $item): ?>
        <div class="movie-card">
          <a href="<?= $item['type'] === 'movie' ? 'rate.php?id=' : 'rateSerie.php?id=' ?><?= $item['id'] ?>">
            <?php if ($item['poster_path']): ?>
              <img src="https://image.tmdb.org/t/p/w200<?= htmlspecialchars($item['poster_path'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= $item['title'] ?>">
            <?php else: ?>
              <p>[Sin imagen]</p>
            <?php endif; ?>
            <p><?= $item['title'] ?></p>
          </a>
          <button class="fav-remove-btn" data-id="<?= $item['id'] ?>" data-type="<?= $item['type'] ?>">💔 Quitar</button>
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

    // Script para eliminar favoritos sin recargar
    document.querySelectorAll('.fav-remove-btn').forEach(button => {
      button.addEventListener('click', async () => {
        const favId = button.getAttribute('data-id');
        const type = button.getAttribute('data-type');

        const confirmRemove = confirm("¿Seguro que quieres quitarlo de tus favoritos?");
        if (!confirmRemove) return;

        try {
          const response = await fetch('remove_favorite_ajax.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `remove_fav_id=${favId}&type=${type}`
          });

          const result = await response.json();
          if (result.success) {
            button.closest('.movie-card').remove();
          } else {
            alert("No se pudo quitar el favorito.");
          }
        } catch (err) {
          console.error(err);
          alert("Error al comunicarse con el servidor.");
        }
      });
    });
  </script>
</body>
</html>
