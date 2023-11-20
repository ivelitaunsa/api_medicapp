<?php

// Importanto variable $con que contiene la conexion con mysql
require_once "../connection.php";

// El dato objetivo
$codigoUsuario = $_POST['codigoUsuario'];

// Enviar datos propios
$esAdmin = $_POST['esAdmin'];

if (!empty($esAdmin) && $esAdmin != 0) {
    try {
        $bloquear_usuario = $con->query("UPDATE USUARIO SET esBloqueado = 1 WHERE codigoUsuario = $codigoUsuario");
        if ($bloquear_usuario->rowCount() == 1) {
            $actualizar_usuario = $con->query("SELECT * FROM USUARIO WHERE codigoUsuario = '$codigoUsuario'");

            echo json_encode($actualizar_usuario->fetch(PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
        } else {
            echo json_encode(['error' => ['message' => 'Error al seleccionar registro actualizado']]);
        }
    } catch (PDOException $e) {
        echo json_encode(['error' => ['message' => 'Error al bloquear usuario']]);
    }
} else {
    echo json_encode(['error' => ['message' => 'No tiene los permisos para hacer esa accion']]);
}
$bloquear_usuario = null;
