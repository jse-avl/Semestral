<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $action = $_POST['action'] ?? '';

        // LOGIN
        if ($action === 'login') {
            try {
                $email = $_POST['email'] ?? '';
                $password = $_POST['password'] ?? '';

                // Buscar primero en la tabla de admins
                try {
                    $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = ?");
                    $stmt->execute([$email]);
                    $admin = $stmt->fetch();

                    if ($admin && password_verify($password, $admin['password'])) {
                        if ($admin['status'] !== 'active') {
                            header("Location: login.php?error=locked");
                            exit;
                        }

                        $_SESSION['user'] = $admin['username'];
                        $_SESSION['user_id'] = $admin['id'];
                        $_SESSION['role'] = 'admin';
                        header("Location: admin/dashboard.php");
                        exit;
                    }
                } catch (PDOException $e) {
                    error_log("Admin login error: " . $e->getMessage());
                    header("Location: login.php?error=system");
                    exit;
                }

                // Buscar en la tabla de usuarios normales
                try {
                    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
                    $stmt->execute([$email]);
                    $user = $stmt->fetch();

                    if ($user && password_verify($password, $user['password'])) {
                        $_SESSION['user'] = $user['username'];
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['role'] = 'user';
                        header("Location: index.php");
                        exit;
                    }
                } catch (PDOException $e) {
                    error_log("User login error: " . $e->getMessage());
                    header("Location: login.php?error=system");
                    exit;
                }

                // Credenciales incorrectas
                header("Location: login.php?error=invalid_login");
                exit;
            } catch (Exception $e) {
                error_log("Login process error: " . $e->getMessage());
                header("Location: login.php?error=system");
                exit;
            }
        }

        // 📝 REGISTRO DE USUARIO
        if ($action === 'register') {
            try {
                $username = $_POST['username'] ?? '';
                $email = $_POST['email'] ?? '';
                $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $genre = $_POST['genre'] ?? '28';

                // Validate email format
                if (!filter_var($email, FILTER_VALIDATE_EMAIL) || 
                    !strpos($email, '@') || 
                    !strpos($email, '.com')) {
                    header("Location: login.php?error=invalid_email");
                    exit;
                }

                try {
                    // Verificar duplicados
                    $check = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
                    $check->execute([$email, $username]);

                    if ($check->fetch()) {
                        header("Location: login.php?error=duplicate");
                        exit;
                    }

                    // Insertar en la tabla de usuarios
                    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, favorite_genre) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$username, $email, $password, $genre]);

                    header("Location: login.php");
                    exit;
                } catch (PDOException $e) {
                    error_log("Database error during registration: " . $e->getMessage());
                    header("Location: login.php?error=system");
                    exit;
                }
            } catch (Exception $e) {
                error_log("Registration process error: " . $e->getMessage());
                header("Location: login.php?error=system");
                exit;
            }
        }
    } catch (Exception $e) {
        error_log("General authentication error: " . $e->getMessage());
        header("Location: login.php?error=system");
        exit;
    }
}