<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: ../login.php');
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Panel de Administración</title>
</head>
<body>
  <h1>Bienvenido, <?= htmlspecialchars($_SESSION['user']) ?> 👑</h1>

  <ul>
    <li><a href="admin_comments.php">📝 Mod. Comentarios</a></li>
    <li><a href="admin_users.php">👥 Usuarios</a></li>
    <li><a href="admin_logs.php">🕵️ Auditoría</a></li>
    <li><a href="admin_stats.php">📊 Estadísticas</a></li>
    <li><a href="admin_featured.php">🌟 Películas Destacadas</a></li>
    <li><a href="logout_admin.php">🚪 Cerrar sesión</a></li>
  </ul>
</body>
</html>
