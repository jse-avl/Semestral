<?php
require '../db.php';

$username = 'admin1';
$password = password_hash('123456789', PASSWORD_DEFAULT);
$email = 'admin1@RateMyMovie.com';

$stmt = $pdo->prepare("INSERT INTO admins (username, password, email) VALUES (?, ?, ?)");
$stmt->execute([$username, $password, $email]);

echo "Admin creado.";
?>