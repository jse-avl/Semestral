<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user']) || !isset($_POST['movie_id'])) {
    http_response_code(403);
    exit;
}

$stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
$stmt->execute([$_SESSION['user']]);
$userId = $stmt->fetchColumn();
$movieId = (int) $_POST['movie_id'];

$stmt = $pdo->prepare("DELETE FROM favorites WHERE user_id = ? AND movie_id = ?");
$stmt->execute([$userId, $movieId]);

echo "ok";
?>
