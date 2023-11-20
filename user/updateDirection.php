<?php

// Importanto variable $con que contiene la conexion con mysql
require_once "../connection.php";

$codigoUsuario = $_POST['codigoUsuario'];
$direccion = limpiar_cadena($_POST['direccion']);

// Verificar direccion
if(!empty($direccion)) {
    try {
        $actualizar_direccion = $con->query("UPDATE USUARIO SET direccion = '$direccion' WHERE codigoUsuario = $codigoUsuario");
        if ($actualizar_direccion->rowCount() == 1) {
            $actualizar_usuario = $con->query("SELECT * FROM USUARIO WHERE codigoUsuario = '$codigoUsuario'");
    
            echo json_encode($actualizar_usuario->fetch(PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
        } else {
            echo json_encode(['error' => ['message' => 'Error al seleccionar registro actualizado']]);
        }
    } catch (PDOException $e) {
        echo json_encode(['error' => ['message' => 'Error en la base de datos']]);
    }
} else {
    echo json_encode(['error' => ['message' => 'Campos obligatorios faltantes']]);
}
$cerrar_sesion=null;