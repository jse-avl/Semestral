<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'db.php';

class MovieService {
    public function getMovieRatings($params) {
        global $pdo;
        
        $movieId = isset($params['movieId']) ? (int)$params['movieId'] : 0;
        
        error_log("SOAP Request - MovieID: $movieId");
        
        try {
            $stmt = $pdo->prepare("
                SELECT u.username, r.rating, r.comment
                FROM ratings r
                JOIN users u ON r.user_id = u.id
                WHERE r.movie_id = ?
            ");
            $stmt->execute([$movieId]);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            error_log("SOAP Response - Result count: " . count($result));
            
            return json_encode($result);
        } catch (PDOException $e) {
            error_log("SOAP Error: " . $e->getMessage());
            return json_encode(['error' => $e->getMessage()]);
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
    // Si se accede directamente al archivo sin una solicitud SOAP
    if (!isset($argv)) {
        echo "<h1>Servidor SOAP</h1>";
        echo "<p>Este archivo implementa un servicio SOAP. No debe accederse directamente.</p>";
        echo "<p><a href='test_ratings_soap.php'>Ir a la página de prueba</a></p>";
    }
}