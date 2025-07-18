<?php
require '../db.php';
header('Content-Type: application/json');

$user = $_GET['username'] ?? '';

$stmtUser = $pdo->prepare("SELECT id FROM users WHERE username = ?");
$stmtUser->execute([$user]);
$userId = $stmtUser->fetchColumn();

if (!$userId) {
    echo json_encode(["error" => "Usuario no válido"]);
    exit;
}

$stmt = $pdo->prepare("SELECT movie_id, rating, comment FROM ratings WHERE user_id = ?");
$stmt->execute([$userId]);

echo json_encode($stmt->fetchAll());