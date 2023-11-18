<?php

// Importanto variable $con que contiene la conexion con mysql
require_once "../connection.php";

$codigoUsuario = $_POST['codigoUsuario'];

try {
    $cerrar_sesion = $con->query("UPDATE USUARIO SET esAutenticado = 0 WHERE codigoUsuario = $codigoUsuario");
    echo json_encode($cerrar_sesion->fetch(PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
} catch (PDOException $e) {
    echo json_encode(['error' => ['message' => 'Error al cerrar sesion']]);
}
$cerrar_sesion=null;