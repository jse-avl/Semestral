<?php
session_start();
require '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_id'], $_POST['movie_id'])) {
    $commentId = (int) $_POST['comment_id'];
    $movieId = (int) $_POST['movie_id'];

    if ($_SESSION['role'] === 'admin') {
        try {
            // Usar el servicio SOAP para eliminar
            $soapClient = new SoapClient("http://localhost/Semestral/service.wsdl", [
                'cache_wsdl' => WSDL_CACHE_NONE
            ]);

            $response = $soapClient->__soapCall("deleteComment", [["commentId" => $commentId]]);
            $result = json_decode($response, true);

            if ($result['status'] === 'success') {
                header("Location: /Semestral/rate.php?id=$movieId&deleted=1");
                exit;
            } else {
                header("Location: /Semestral/rate.php?id=$movieId&error=" . urlencode($result['message']));
                exit;
            }
        } catch (SoapFault $e) {
            header("Location: /Semestral/rate.php?id=$movieId&error=" . urlencode($e->getMessage()));
            exit;
        }
    } else {
        header("Location: /Semestral/rate.php?id=$movieId&error=No autorizado");
        exit;
    }
} else {
    $movieId = isset($_POST['movie_id']) ? (int) $_POST['movie_id'] : 0;
    header("Location: /Semestral/rate.php?id=$movieId&error=Solicitud inválida");
    exit;
}