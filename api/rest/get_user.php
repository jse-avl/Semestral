<?php
require '../db.php';
header('Content-Type: application/json');

$username = $_GET['username'] ?? '';

$stmt = $pdo->prepare("SELECT username, email, favorite_genre FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode($user ?: ["error" => "Usuario no encontrado"]);