<?php

// Importanto variable $con que contiene la conexion con mysql
require_once "../connection.php";

// El dato objetivo
$codigoUsuario = $_POST['codigoUsuario'];

// Enviar datos propios
$esAdmin= $_POST['esAdmin'];

if (!empty($esAdmin) && $esAdmin !=0) {
    try {
        $bloquear_usuario = $con->query("UPDATE USUARIO SET esBloqueado = 1 WHERE codigoUsuario = $codigoUsuario");
        echo json_encode($bloquear_usuario->fetch(PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
    } catch (PDOException $e) {
        echo json_encode(['error' => ['message' => 'Error al bloquear usuario']]);
    }
} else {
    echo json_encode(['error' => ['message' => 'No tiene los permisos para hacer esa accion']]);
}
$bloquear_usuario=null;
