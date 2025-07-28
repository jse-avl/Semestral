<?php
include dirname(__DIR__) . '/db.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$token = "eyJhbGciOiJIUzI1NiJ9.eyJhdWQiOiJmOTk0NzY5NDA0M2EyNTY3ZmE1ZWUwY2Q4OTA2ZGEwOCIsIm5iZiI6MTc1MTM5ODU3NS42NDMwMDAxLCJzdWIiOiI2ODY0MzhhZjUxZjc3OThkZTRkMTI1MDEiLCJzY29wZXMiOlsiYXBpX3JlYWQiXSwidmVyc2lvbiI6MX0.vx91c1ypvxjGRzz26P_SNsnqPAOGLEFou3Lo8dGKU58";

// Get content type from query parameter, default to movies
$contentType = $_GET['type'] ?? 'movie';
$url = "https://api.themoviedb.org/3/{$contentType}/popular?language=es-ES&page=1";

$headers = [
    "Authorization: Bearer $token",
    "Accept: application/json"
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);
$content = $data['results'] ?? [];

// Get blocked IDs
$blocked = $pdo->query("SELECT movie_id FROM blocked_movies")->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Contenido (API)</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <style>
        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        body.light .container {
            border: 1px solid #eee;
        }
        body.dark .container {
            background-color:var(--dark-bg);  
            color: var(--dark-text);
            border: 1px solid var(--dark-shadow);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 12px 16px;
            border: 1px solid var(--dark-shadow); 
            text-align: left;
            vertical-align: top;
            line-height: 1.4;
        }
        body.light th {
            background-color: var(--light-bg);  
            border-bottom: 2px solid #dee2e6;
        }
        body.dark th {
            background-color: var(--dark-hover);
            border-bottom: 2px solid #555;
        }
        h2 {
            margin-bottom: 25px;
            font-size: 1.8em;
            color: inherit;
        }
        a.block-link {
            color: #dc3545;
            text-decoration: none;
            font-weight: 600;
            padding: 4px 8px;
            border-radius: 4px;
            transition: all 0.3s ease;
        }
        a.unblock-link {
            color: #28a745;
            text-decoration: none;
            font-weight: 600;
            padding: 4px 8px;
            border-radius: 4px;
            transition: all 0.3s ease;
        }
        a.block-link:hover, a.unblock-link:hover {
            text-decoration: none;
            background-color: rgba(0,0,0,0.05);
            transform: translateY(-1px);
        }
        .content-toggle {
            margin-bottom: 30px;
            display: flex;
            gap: 12px;
        }
        .content-toggle a {
            padding: 10px 20px;
            text-decoration: none;
            color: #555;
            background: #f8f9fa;
            border-radius: 6px;
            transition: all 0.3s ease;
            border: 1px solid var(--dark-shadow);
        }
        .content-toggle a.active {
            background: var(--light-hover);
            color: white;
            border-color: var(--light-hover);
            box-shadow: 0 2px 4px rgba(13,110,253,0.2);
        }
        .content-toggle a:hover:not(.active) {
            background: var(--dark-hover);
            color: white;
            transform: translateY(-1px);
        }
    </style>
</head>
<body>

    <?php include '../includes/navbar.php'; ?>

    <div class="container">
        <div class="content-toggle">
            <a href="?type=movie" class="<?= $contentType === 'movie' ? 'active' : '' ?>">Películas</a>
            <a href="?type=tv" class="<?= $contentType === 'tv' ? 'active' : '' ?>">Series</a>
        </div>
        
        <h2><?= $contentType === 'movie' ? 'Películas' : 'Series' ?> Populares desde TMDB</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Título</th>
                <th>Fecha</th>
                <th>Descripción</th>
                <th>Acciones</th>
            </tr>
            <?php foreach ($content as $item): ?>
            <tr>
                <td><?= $item['id'] ?></td>
                <td><?= htmlspecialchars($item[$contentType === 'movie' ? 'title' : 'name']) ?></td>
                <td><?= htmlspecialchars($item[$contentType === 'movie' ? 'release_date' : 'first_air_date'] ?? 'N/A') ?></td>
                <td><?= nl2br(htmlspecialchars($item['overview'])) ?></td>
                <td>
                    <a
                        class="<?= in_array($item['id'], $blocked) ? 'unblock-link' : 'block-link' ?>"
                        href="toggle_block_movie.php?id=<?= $item['id'] ?>&type=<?= $contentType ?>"
                        onclick="return confirm('¿<?= in_array($item['id'], $blocked) ? 'Desbloquear' : 'Bloquear' ?> este contenido del catálogo?')"
                    >
                        <?= in_array($item['id'], $blocked) ? 'Desbloquear' : 'Bloquear' ?>
                    </a>
                </td>
            </tr>
            <?php endforeach ?>
        </table>
    </div>
    
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
      <img src="../css/logo2.png" alt="Logo" />
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