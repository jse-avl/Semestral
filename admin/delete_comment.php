<?php
session_start();
require '../db.php';

// Check if request is POST and from the same domain
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SERVER['HTTP_REFERER']) || 
    parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST) !== $_SERVER['HTTP_HOST']) {
    header("HTTP/1.1 403 Forbidden");
    exit();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

if (isset($_POST['comment_id']) && (isset($_POST['movie_id']) || isset($_POST['serie_id']))) {
    $commentId = (int) $_POST['comment_id'];
    $movieId = isset($_POST['movie_id']) ? (int) $_POST['movie_id'] : null;
    $serieId = isset($_POST['serie_id']) ? (int) $_POST['serie_id'] : null;
    
    $returnPath = $movieId !== null ? "rate.php?id=$movieId" : "rateSerie.php?id=$serieId";

    if ($_SESSION['role'] === 'admin') {
        try {
            // Usar el servicio SOAP para eliminar
            $soapClient = new SoapClient("http://localhost/Semestral/service.wsdl", [
                'cache_wsdl' => WSDL_CACHE_NONE
            ]);

            $response = $soapClient->__soapCall("deleteComment", [["commentId" => $commentId]]);
            $result = json_decode($response, true);

            if ($result['status'] === 'success') {
                header("Location: /Semestral/$returnPath&deleted=1");
                exit;
            } else {
                header("Location: /Semestral/$returnPath&error=" . urlencode($result['message']));
                exit;
            }
        } catch (SoapFault $e) {
            header("Location: /Semestral/$returnPath&error=" . urlencode($e->getMessage()));
            exit;
        }
    } else {
        header("Location: /Semestral/$returnPath&error=No autorizado");
        exit;
    }
} else {
    $returnId = isset($_POST['movie_id']) ? (int) $_POST['movie_id'] : (isset($_POST['serie_id']) ? (int) $_POST['serie_id'] : 0);
    $returnPath = isset($_POST['movie_id']) ? "rate.php?id=$returnId" : "rateSerie.php?id=$returnId";
    header("Location: /Semestral/$returnPath&error=Solicitud inválida");
    exit;
}