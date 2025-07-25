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
    <style>
        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            margin-bottom: 20px;
            color: #333;
            font-size: 24px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px 14px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #efefef;
        }
        a.action-link {
            color: #007acc;
            text-decoration: none;
            font-weight: bold;
        }
        a.action-link:hover {
            text-decoration: underline;
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
</body>
</html>