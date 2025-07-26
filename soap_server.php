<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'db.php';

class MovieService {
    public function getMovieRatings($params) {
        global $pdo;

        $movieId = isset($params['movieId']) ? (int)$params['movieId'] : 0;
        $serieId = isset($params['serieId']) ? (int)$params['serieId'] : 0;

        error_log("SOAP Request - MovieID: $movieId, SerieID: $serieId");

        try {
            if ($movieId > 0) {
                $stmt = $pdo->prepare("
                    SELECT r.id, u.username, r.rating, r.comment, r.created_at
                    FROM ratings r
                    JOIN users u ON r.user_id = u.id
                    WHERE r.movie_id = ?
                    ORDER BY r.id DESC
                ");
                $stmt->execute([$movieId]);
            } elseif ($serieId > 0) {
                $stmt = $pdo->prepare("
                    SELECT r.id, u.username, r.rating, r.comment, r.created_at
                    FROM ratings r
                    JOIN users u ON r.user_id = u.id
                    WHERE r.serie_id = ?
                    ORDER BY r.id DESC
                ");
                $stmt->execute([$serieId]);
            } else {
                return json_encode(['error' => 'Debe proporcionar movieId o serieId']);
            }

            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("SOAP Response - Result count: " . count($result));

            return json_encode($result);
        } catch (PDOException $e) {
            error_log("SOAP Error: " . $e->getMessage());
            return json_encode(['error' => $e->getMessage()]);
        }
    }

    public function deleteComment($params) {
        global $pdo;

        $commentId = isset($params['commentId']) ? (int)$params['commentId'] : 0;
        error_log("SOAP Delete Request - CommentID: $commentId");

        try {
            $stmt = $pdo->prepare("DELETE FROM ratings WHERE id = ?");
            $stmt->execute([$commentId]);
            return json_encode(['status' => 'success', 'message' => 'Comentario eliminado']);
        } catch (PDOException $e) {
            error_log("SOAP Delete Error: " . $e->getMessage());
            return json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}

// Verificar si es una solicitud SOAP
if (!isset($argv) && isset($_SERVER['REQUEST_METHOD'])) {
    // Crear el servidor SOAP
    $server = new SoapServer(__DIR__ . "/service.wsdl", [
        'cache_wsdl' => WSDL_CACHE_NONE,
        'soap_version' => SOAP_1_1
    ]);

    // Registrar la clase de servicio
    $server->setClass("MovieService");

    // Procesar la solicitud SOAP
    $server->handle();
} else {
    if (!isset($argv)) {
        echo "<h1>Servidor SOAP</h1>";
        echo "<p>Este archivo implementa un servicio SOAP. No debe accederse directamente.</p>";
        echo "<p><a href='test_ratings_soap.php'>Ir a la página de prueba</a></p>";
    }
}
