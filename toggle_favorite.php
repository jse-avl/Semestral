<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user'], $_POST['movie_id'])) {
    header("Location: index.php");
    exit;
}

$stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
$stmt->execute([$_SESSION['user']]);
$userId = $stmt->fetchColumn();
$movieId = (int) $_POST['movie_id'];

// Verifica si ya es favorito
$check = $pdo->prepare("SELECT 1 FROM favorites WHERE user_id = ? AND movie_id = ?");
$check->execute([$userId, $movieId]);

if ($check->fetch()) {
    $del = $pdo->prepare("DELETE FROM favorites WHERE user_id = ? AND movie_id = ?");
    $del->execute([$userId, $movieId]);
} else {
    $add = $pdo->prepare("INSERT INTO favorites (user_id, movie_id) VALUES (?, ?)");
    $add->execute([$userId, $movieId]);
}

header("Location: index.php");
exit;
