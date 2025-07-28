<?php
include dirname(__DIR__) . '/db.php';
session_start();

// Validar acceso
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Obtener usuarios
$stmt = $pdo->prepare("SELECT * FROM users ORDER BY created_at DESC");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Administrar Usuarios</title>
    <link rel="stylesheet" href="../css/style.css">
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <style>
        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        body.light .container {
            border: 1px solid #eaeaea;
        }
        body.dark .container {
            background-color: var(--dark-bg);
            color: var(--dark-text);
            border: 1px solid var(--dark-shadow);
        }
        body.light h2 {
            color: #2c3e50;
        }
        body.dark h2 {
            color: #ecf0f1;
        }
        h2 {
            margin-bottom: 25px;
            font-size: 28px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 15px;
        }
        th, td {
            padding: 12px 16px;
            text-align: left;
            border: 1px solid;
            transition: all 0.3s ease;
        }
        body.light th, body.light td {
            border-color: #e0e0e0;
        }
        body.dark th, body.dark td {
            border-color: var(--dark-shadow);
        }
        body.light th {
            background-color: #f8f9fa;
            color: var(--light-navbar);
        }
        body.dark th {
            background-color: var(--dark-hover);
            color: var(--dark-text);  
        }
        a.action-link {
            color: #3498db;
            text-decoration: none;
            font-weight: 600;
            padding: 4px 8px;
            border-radius: 4px;
            transition: all 0.2s ease;
        }
        body.dark a.action-link {
            color: #5dade2;
        }
        a.action-link:hover {
            background-color: rgba(52, 152, 219, 0.1);
            text-decoration: none;
        }
        body.dark a.action-link:hover {
            background-color: rgba(93, 173, 226, 0.2);
        }
    </style>
</head>
<body>

    <?php include '../includes/navbar.php'; ?>

    <div class="container">
        <h2>Gestión de Usuarios</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Usuario</th>
                <th>Email</th>
                <th>Rol</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
            <?php foreach ($users as $u): ?>
            <tr>
                <td><?= $u['id'] ?></td>
                <td><?= htmlspecialchars($u['username']) ?></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><?= $u['role'] ?></td>
                <td><?= $u['status'] ?></td>
                <td>
                    <a class="action-link" href="edit_user.php?id=<?= $u['id'] ?>">Editar</a> |
                    <a class="action-link" href="delete_user.php?id=<?= $u['id'] ?>" onclick="return confirm('¿Eliminar este usuario?')">Eliminar</a> |
                    <a class="action-link" href="toggle_status.php?id=<?= $u['id'] ?>">Cambiar Estado</a>
                </td>
            </tr>
            <?php endforeach ?>
        </table>
    </div>
    
<!-- Swiper + Temas -->
<script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
<script>
  new Swiper('.swiper', {
    slidesPerView: 3,
    spaceBetween: 10,
    loop: true,
    pagination: { el: '.swiper-pagination' },
    autoplay: { delay: 3000 }
  });

  function toggleTheme() {
    const isDark = document.body.classList.contains('dark');
    const next = isDark ? 'light' : 'dark';
    document.body.classList.remove('light', 'dark');
    document.body.classList.add(next);
    document.cookie = "theme=" + next + "; path=/; max-age=31536000";
    document.getElementById('themeToggle').checked = next === 'dark';
  }

  function applyThemeFromCookie() {
    const match = document.cookie.match(/theme=(light|dark)/);
    const theme = match ? match[1] : 'light';
    document.body.classList.add(theme);
    if (theme === 'dark') {
      document.getElementById('themeToggle').checked = true;
    }
  }
  applyThemeFromCookie();
</script>

<script>
  function toggleMenu() {
    const menu = document.querySelector('.ul');
    menu.classList.toggle('show');
  }
</script>

<!-- Footer -->
<footer class="main-footer">
  <div class="footer-container">
    <div class="footer-logo">
   <img src="../css/logo2.png" alt="Logo" />
      <h3>RateMyMovie</h3>
    </div>
    <div class="footer-social">
      <p>© <?= date('Y') ?> RateMyMovie. Todos los derechos reservados.</p>
      <div class="social-icons">
        <a href="https://facebook.com" target="_blank" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
        <a href="https://twitter.com" target="_blank" aria-label="Twitter"><i class="fab fa-x-twitter"></i></a>
        <a href="https://instagram.com" target="_blank" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
      </div>
    </div>
  </div>
</footer>
</body>
</html>