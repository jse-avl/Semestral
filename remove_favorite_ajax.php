<?php
session_start();
require 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user']) || !isset($_POST['remove_fav_id']) || !isset($_POST['type'])) {
  echo json_encode(['success' => false]);
  exit;
}

// Obtener ID de usuario
$stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
$stmt->execute([$_SESSION['user']]);
$userId = $stmt->fetchColumn();

$favId = filter_var($_POST['remove_fav_id'], FILTER_VALIDATE_INT);
$type = $_POST['type'];
$column = $type === 'movie' ? 'movie_id' : 'serie_id';

if ($favId && in_array($type, ['movie', 'serie'])) {
  $stmtDel = $pdo->prepare("DELETE FROM favorites WHERE user_id = ? AND $column = ?");
  $stmtDel->execute([$userId, $favId]);
  echo json_encode(['success' => true]);
} else {
  echo json_encode(['success' => false]);
}
exit;
