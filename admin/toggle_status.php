<?php
include dirname(__DIR__) . '/db.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id || !is_numeric($id)) {
    echo "ID inválido.";
    exit;
}

$stmt = $pdo->prepare("SELECT status FROM users WHERE id = ?");
$stmt->execute([$id]);
$status = $stmt->fetchColumn();

if ($status === false) {
    echo "Usuario no encontrado.";
    exit;
}

$new_status = ($status === 'active') ? 'suspended' : 'active';

$stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
$stmt->execute([$new_status, $id]);

header("Location: admin_users.php");
exit;