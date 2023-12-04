<?php

require_once "../connection.php";

try {
    $stmt = $con->query("SELECT * FROM PEDIDO_CABECERA");
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
} catch (PDOException $e) {
    echo json_encode(['error' => ['message' => 'Error en la base de datos']]);
    die();
}
