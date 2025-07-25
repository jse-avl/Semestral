<?php
include dirname(__DIR__) . '/db.php';
session_start();

// Validación del admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id || !is_numeric($id)) {
    echo "ID inválido.";
    exit;
}

// Verificar si ya está bloqueado
$stmt = $pdo->prepare("SELECT COUNT(*) FROM blocked_movies WHERE movie_id = ?");
$stmt->execute([$id]);
$isBlocked = $stmt->fetchColumn();

if ($isBlocked) {
    // Desbloquear
    $stmt = $pdo->prepare("DELETE FROM blocked_movies WHERE movie_id = ?");
    $stmt->execute([$id]);
} else {
    // Bloquear
    $stmt = $pdo->prepare("INSERT INTO blocked_movies (movie_id) VALUES (?)");
    $stmt->execute([$id]);
}

header("Location: admin_movies.php");
exit;