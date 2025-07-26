
<?php
session_start();
require 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user']) || (!isset($_POST['movie_id']) && !isset($_POST['serie_id']))) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
$stmt->execute([$_SESSION['user']]);
$userId = $stmt->fetchColumn();

$response = ['status' => 'error', 'message' => 'Unknown'];

if (isset($_POST['movie_id'])) {
    $movieId = (int) $_POST['movie_id'];

    $check = $pdo->prepare("SELECT 1 FROM favorites WHERE user_id = ? AND movie_id = ?");
    $check->execute([$userId, $movieId]);

    if ($check->fetch()) {
        $del = $pdo->prepare("DELETE FROM favorites WHERE user_id = ? AND movie_id = ?");
        $del->execute([$userId, $movieId]);
        $response = ['status' => 'removed', 'type' => 'movie', 'id' => $movieId];
    } else {
        $add = $pdo->prepare("INSERT INTO favorites (user_id, movie_id) VALUES (?, ?)");
        $add->execute([$userId, $movieId]);
        $response = ['status' => 'added', 'type' => 'movie', 'id' => $movieId];
    }

} elseif (isset($_POST['serie_id'])) {
    $serieId = (int) $_POST['serie_id'];

    $check = $pdo->prepare("SELECT 1 FROM favorites WHERE user_id = ? AND serie_id = ?");
    $check->execute([$userId, $serieId]);

    if ($check->fetch()) {
        $del = $pdo->prepare("DELETE FROM favorites WHERE user_id = ? AND serie_id = ?");
        $del->execute([$userId, $serieId]);
        $response = ['status' => 'removed', 'type' => 'serie', 'id' => $serieId];
    } else {
        $add = $pdo->prepare("INSERT INTO favorites (user_id, serie_id) VALUES (?, ?)");
        $add->execute([$userId, $serieId]);
        $response = ['status' => 'added', 'type' => 'serie', 'id' => $serieId];
    }
}

echo json_encode($response);
exit;
