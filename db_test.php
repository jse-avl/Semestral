<?php
require 'db.php';

$movieId = 1061474;

$stmt = $pdo->prepare("
    SELECT u.username, r.rating, r.comment
    FROM ratings r
    JOIN users u ON r.user_id = u.id
    WHERE r.movie_id = ?
");
$stmt->execute([$movieId]);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<pre>";
print_r($result);
echo "</pre>";