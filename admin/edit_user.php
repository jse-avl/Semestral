<?php
include dirname(__DIR__) . '/db.php';
session_start();

// Solo admins pueden acceder
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    echo "ID de usuario no proporcionado.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $role = $_POST['role'] ?? 'user';

    $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, role = ? WHERE id = ?");
    $stmt->execute([$username, $email, $role, $id]);

    header("Location: admin_users.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    echo "Usuario no encontrado.";
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Usuario</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        form { max-width: 400px; }
        label { display: block; margin-top: 10px; }
        input, select, button { width: 100%; padding: 8px; margin-top: 4px; }
        button { background-color: #2d89ef; color: white; border: none; margin-top: 20px; cursor: pointer; }
        button:hover { background-color: #1b5fbe; }
    </style>
</head>
<body>
<h2>Editar Usuario</h2>
<form method="POST">
    <label>Usuario:
        <input name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
    </label>
    <label>Email:
        <input name="email" type="email" value="<?= htmlspecialchars($user['email']) ?>" required>
    </label>
    <label>Rol:
        <select name="role" required>
            <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>User</option>
            <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
        </select>
    </label>
    <button type="submit">Guardar</button>
</form>
</body>
</html>