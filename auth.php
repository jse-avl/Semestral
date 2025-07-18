<?php
require 'db.php';
session_start();

function sanitize($value) {
  return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = sanitize($_POST['username'] ?? '');
  $email    = sanitize($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';
  $genre    = isset($_POST['genre']) ? (int) $_POST['genre'] : null;
  $action   = $_POST['action'] ?? '';

  $_SESSION['login_attempts'] = $_SESSION['login_attempts'] ?? 0;

  if ($action === 'login') {
    if ($_SESSION['login_attempts'] >= 5) {
      header('Location: login.php?error=locked');
      exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
      $_SESSION['user']  = $user['username'];
      $_SESSION['genre'] = $user['favorite_genre'];
      $_SESSION['role']  = $user['role'] ?? 'user';

      setcookie('user', $user['username'], time() + 604800, '/', '', false, true);
      setcookie('genre', $user['favorite_genre'], time() + 604800, '/', '', false, true);
      setcookie('role', $_SESSION['role'], time() + 604800, '/', '', false, true);

      $_SESSION['login_attempts'] = 0;
      header('Location: index.php');
      exit;
    } else {
      $_SESSION['login_attempts'] += 1;
      header('Location: login.php?error=invalid_login');
      exit;
    }

  } elseif ($action === 'register') {
    $stmtCheck = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
    $stmtCheck->execute([$email, $username]);

    if ($stmtCheck->rowCount() > 0) {
      header('Location: login.php?error=duplicate');
      exit;
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $defaultRole = 'user';

    $stmtInsert = $pdo->prepare("
      INSERT INTO users (username, email, password, favorite_genre, role)
      VALUES (?, ?, ?, ?, ?)
    ");
    $stmtInsert->execute([$username, $email, $hash, $genre, $defaultRole]);

    $_SESSION['user']  = $username;
    $_SESSION['genre'] = $genre;
    $_SESSION['role']  = $defaultRole;

    setcookie('user', $username, time() + 604800, '/', '', false, true);
    setcookie('genre', $genre, time() + 604800, '/', '', false, true);
    setcookie('role', $defaultRole, time() + 604800, '/', '', false, true);

    header('Location: index.php');
    exit;
  }
} else {
  header('Location: login.php');
  exit;
}