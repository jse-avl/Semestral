<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'login') {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        // Buscar primero en la tabla de admins
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = ?");
        $stmt->execute([$email]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            if ($admin['status'] !== 'active') {
                header("Location: login.php?error=locked");
                exit;
            }

            $_SESSION['admin'] = true;
            $_SESSION['user'] = $admin['username'];
            $_SESSION['user_id'] = $admin['id'];
            header("Location: admin/admin_panel.php");
            exit;
        }

        // Si no es admin, buscar en usuarios normales
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['admin'] = false;
            $_SESSION['user'] = $user['username'];
            $_SESSION['user_id'] = $user['id'];
            header("Location: index.php");
            exit;
        }

        header("Location: login.php?error=invalid_login");
        exit;
    }

    if ($action === 'register') {
        // Registro para usuarios normales (no admins)
        $username = $_POST['username'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $genre = $_POST['genre'] ?? '28';

        // Verifica duplicados
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
        $check->execute([$email, $username]);

        if ($check->fetch()) {
            header("Location: login.php?error=duplicate");
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, favorite_genre) VALUES (?, ?, ?, ?)");
        $stmt->execute([$username, $email, $password, $genre]);

        header("Location: login.php");
        exit;
    }
}
