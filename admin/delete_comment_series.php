<?php
session_start();
require '../db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: /Semestral/login.php');
    exit;
}

$commentId = isset($_POST['comment_id']) ? (int)$_POST['comment_id'] : 0;
$serieId   = isset($_POST['serie_id']) ? (int)$_POST['serie_id'] : 0;

if ($commentId > 0 && $serieId > 0) {
    $stmt = $pdo->prepare("DELETE FROM ratings WHERE id = ? AND movie_id = ?");
    $stmt->execute([$commentId, $serieId]);
}

header("Location: /Semestral/rateSerie.php?id=$serieId");
exit;
?>