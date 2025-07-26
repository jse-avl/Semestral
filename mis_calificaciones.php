<?php
session_start();
require 'db.php';
require 'tmdb.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$stmtUser = $pdo->prepare("SELECT id FROM users WHERE username = ?");
$stmtUser->execute([$_SESSION['user']]);
$userId = $stmtUser->fetchColumn();

if (!$userId) {
    header('Location: login.php');
    exit;
}

// Validación segura para actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ratingId = filter_var($_POST['rating_id'] ?? 0, FILTER_VALIDATE_INT);
    $newRating = filter_var($_POST['rating'] ?? 0, FILTER_VALIDATE_INT);
    $comment = trim($_POST['comment'] ?? '');
    $comment = htmlspecialchars($comment, ENT_QUOTES, 'UTF-8');

    if ($ratingId && $newRating >= 1 && $newRating <= 5) {
        $stmtUpdate = $pdo->prepare("UPDATE ratings SET rating = ?, comment = ? WHERE id = ? AND user_id = ?");
        $stmtUpdate->execute([$newRating, $comment, $ratingId, $userId]);
        $success = "Valoración actualizada.";
    }
}

// Obtener valoraciones seguras
$stmt = $pdo->prepare("
    SELECT r.id AS rating_id, r.rating, r.comment, r.movie_id, r.serie_id
    FROM ratings r
    WHERE r.user_id = ?
");
$stmt->execute([$userId]);
$ratings = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Mis Calificaciones</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>
<body>
  <?php include 'includes/navbar.php'; ?>

  <h2>Mis Calificaciones</h2>

  <?php if (isset($success)): ?>
    <p class="p" style="color:green;"><?= $success ?></p>
  <?php endif; ?>

  <?php if (empty($ratings)): ?>
    <p class="p">No has valorado ninguna película aún.</p>
  <?php endif; ?>

  <?php foreach ($ratings as $r): ?>
    <?php
    if ($r['movie_id']) {
        $movie = getMovieById($r['movie_id']);
        $title = $movie['title'] ?? 'Título no disponible';
        $poster = $movie['poster_path'] ?? '';
    } else {
        $serie = getSerieById($r['serie_id']);
        $title = $serie['name'] ?? 'Título no disponible';
        $poster = $serie['poster_path'] ?? '';
    }
    ?>
    <form method="POST" class="rating-form">
      <div class="movie-card3">
        <img src="https://image.tmdb.org/t/p/w200<?= $poster ?>" alt="<?= htmlspecialchars($title) ?>">
        <h3><?= htmlspecialchars($title) ?></h3>

        <input type="hidden" name="rating_id" value="<?= $r['rating_id'] ?>">
        <label>Calificación:</label>
        <div class="star-select">
          <?php for ($i = 5; $i >= 1; $i--): ?>
            <input type="radio" id="star<?= $r['rating_id'] . '_' . $i ?>" name="rating" value="<?= $i ?>" <?= ($r['rating'] == $i) ? 'checked' : '' ?>>
            <label for="star<?= $r['rating_id'] . '_' . $i ?>">★</label>
          <?php endfor; ?>
        </div>

        <label>Comentario:</label>
        <textarea name="comment" placeholder="Escribe tu opinión..."><?= $r['comment'] ?></textarea>

        <button type="submit">Actualizar</button>
      </div>
    </form>
  <?php endforeach; ?>
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