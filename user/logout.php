<?php

// Importanto variable $con que contiene la conexion con mysql
require_once "../connection.php";

$codigoUsuario = $_POST['codigoUsuario'];

try {
    $cerrar_sesion = $con->query("UPDATE USUARIO SET esAutenticado = 0 WHERE codigoUsuario = $codigoUsuario");
    // Después de realizar el UPDATE
    if ($cerrar_sesion->rowCount() == 1) {
        $actualizar_usuario = $con->query("SELECT * FROM USUARIO WHERE codigoUsuario = '$codigoUsuario'");

        echo json_encode($actualizar_usuario->fetch(PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
    } else {
        echo json_encode(['error' => ['message' => 'Error al seleccionar registro actualizado']]);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => ['message' => 'Error al cerrar sesion']]);
}
$cerrar_sesion = null;
