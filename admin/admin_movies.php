<?php
include dirname(__DIR__) . '/db.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$token = "eyJhbGciOiJIUzI1NiJ9.eyJhdWQiOiJmOTk0NzY5NDA0M2EyNTY3ZmE1ZWUwY2Q4OTA2ZGEwOCIsIm5iZiI6MTc1MTM5ODU3NS42NDMwMDAxLCJzdWIiOiI2ODY0MzhhZjUxZjc3OThkZTRkMTI1MDEiLCJzY29wZXMiOlsiYXBpX3JlYWQiXSwidmVyc2lvbiI6MX0.vx91c1ypvxjGRzz26P_SNsnqPAOGLEFou3Lo8dGKU58";

$url = "https://api.themoviedb.org/3/movie/popular?language=es-ES&page=1";

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
$movies = $data['results'] ?? [];

// Obtener IDs bloqueados
$blocked = $pdo->query("SELECT movie_id FROM blocked_movies")->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Películas (API)</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .container { max-width: 1200px; margin: 40px auto; padding: 20px; background: #fff; box-shadow: 0 0 10px #ccc; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px 14px; border: 1px solid #ddd; text-align: left; vertical-align: top; }
        th { background-color: #efefef; }
        h2 { margin-bottom: 20px; }
        a.block-link { color: crimson; text-decoration: none; font-weight: bold; }
        a.unblock-link { color: green; text-decoration: none; font-weight: bold; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>

    <?php include '../includes/navbar.php'; ?>

    <div class="container">
        <h2>Películas Populares desde TMDB</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Título</th>
                <th>Fecha</th>
                <th>Descripción</th>
                <th>Acciones</th>
            </tr>
            <?php foreach ($movies as $m): ?>
            <tr>
                <td><?= $m['id'] ?></td>
                <td><?= htmlspecialchars($m['title']) ?></td>
                <td><?= htmlspecialchars($m['release_date'] ?? 'N/A') ?></td>
                <td><?= nl2br(htmlspecialchars($m['overview'])) ?></td>
                <td>
                    <a
                        class="<?= in_array($m['id'], $blocked) ? 'unblock-link' : 'block-link' ?>"
                        href="toggle_block_movie.php?id=<?= $m['id'] ?>"
                        onclick="return confirm('¿<?= in_array($m['id'], $blocked) ? 'Desbloquear' : 'Bloquear' ?> esta película del catálogo?')"
                    >
                        <?= in_array($m['id'], $blocked) ? 'Desbloquear' : 'Bloquear' ?>
                    </a>
                </td>
            </tr>
            <?php endforeach ?>
        </table>
    </div>
</body>
</html>