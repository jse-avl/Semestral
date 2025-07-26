<?php
session_start();
require 'db.php';

function sanitize($value) {
    return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
}

function isValidUsername($name) {
    return preg_match('/^[a-zA-Z0-9_]{3,20}$/', $name);
}

function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function isValidPassword($pass) {
    return preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_]).{8,}$/', $pass);
}

// Verificar sesión
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// Obtener datos actuales
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$_SESSION['user']]);
$user = $stmt->fetch();

if (!$user) {
    header('Location: login.php');
    exit;
}

// Procesar actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newUsername = sanitize($_POST['username'] ?? '');
    $newEmail    = sanitize($_POST['email'] ?? '');
    $newGenre    = (int) ($_POST['genre'] ?? 28);
    $newPassword = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $updatePassword = false;

    // Validar entrada
    if (!isValidUsername($newUsername) || !isValidEmail($newEmail)) {
        $error = "Nombre o correo no válidos.";
    } elseif (!empty($newPassword) || !empty($confirmPassword)) {
        if ($newPassword !== $confirmPassword) {
            $error = "Las contraseñas no coinciden.";
        } elseif (!isValidPassword($newPassword)) {
            $error = "La nueva contraseña no cumple los requisitos.";
        } else {
            $updatePassword = true;
        }
    }

    // Actualizar si no hay errores
    if (!isset($error)) {
        $sql = "UPDATE users SET username = ?, email = ?, favorite_genre = ?" . ($updatePassword ? ", password = ?" : "") . " WHERE id = ?";
        $params = [$newUsername, $newEmail, $newGenre];
        if ($updatePassword) {
            $params[] = password_hash($newPassword, PASSWORD_DEFAULT);
        }
        $params[] = $user['id'];

        $update = $pdo->prepare($sql);
        $update->execute($params);

        $_SESSION['user'] = $newUsername;
        $_SESSION['genre'] = $newGenre;
        setcookie('user', $newUsername, time() + 604800, '/', '', false, true);
        setcookie('genre', $newGenre, time() + 604800, '/', '', false, true);

        $success = "Perfil actualizado correctamente.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Editar Perfil</title>
  <link rel="stylesheet" href="css/style.css">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

  <form method="POST" class="login-form">
    <h2>Editar Perfil</h2>

    <?php if (isset($error)): ?>
      <p style="color:red;"><?= $error ?></p>
    <?php elseif (isset($success)): ?>
      <p style="color:green;"><?= $success ?></p>
    <?php endif; ?>

    <label>Nombre:</label>
    <input name="username" value="<?= sanitize($user['username']) ?>" required pattern="[a-zA-Z0-9_]{3,20}">

    <label>Correo electrónico:</label>
    <input name="email" type="email" value="<?= sanitize($user['email']) ?>" required>

    <label>Género favorito:</label>
    <select name="genre" required>
      <option value="28" <?= $user['favorite_genre']==28?'selected':'' ?>>Acción</option>
      <option value="35" <?= $user['favorite_genre']==35?'selected':'' ?>>Comedia</option>
      <option value="18" <?= $user['favorite_genre']==18?'selected':'' ?>>Drama</option>
      <option value="27" <?= $user['favorite_genre']==27?'selected':'' ?>>Terror</option>
      <option value="10749" <?= $user['favorite_genre']==10749?'selected':'' ?>>Romance</option>
    </select>

    <label>Nueva contraseña (opcional):</label>
    <input type="password" name="password" placeholder="Solo si deseas cambiarla">

    <label>Confirmar nueva contraseña:</label>
    <input type="password" name="confirm_password" placeholder="Confirma nueva contraseña">

    <button type="submit">Guardar Cambios</button>
  </form>
  <script>
  function toggleTheme() {
    const current = document.body.classList.contains('dark') ? 'dark' : 'light';
    const next = current === 'dark' ? 'light' : 'dark';
    document.body.classList.remove(current);
    document.body.classList.add(next);
    document.cookie = "theme=" + next + "; path=/; max-age=31536000"; // 1 año
  }

  function applyThemeFromCookie() {
    const match = document.cookie.match(/theme=(light|dark)/);
    const theme = match ? match[1] : 'light';
    document.body.classList.add(theme);
  }

  applyThemeFromCookie();
</script>
</body>
</html>