<?php

// Importanto variable $con que contiene la conexion con mysql
require_once "../connection.php";

$codPedCab = $_POST['codPedCab'];
$codEstEnt = $_POST['codEstEnt'];

// Verificar codEstEnt
if(!empty($codEstEnt)) {
    try {
        $actualizar_estado = $con->query("UPDATE PEDIDO_CABECERA SET codEstEnt = '$codEstEnt' WHERE codPedCab = $codPedCab");
        if ($actualizar_estado->rowCount() == 1) {
            $actualizar_pedido = $con->query("SELECT * FROM PEDIDO_CABECERA WHERE codPedCab = '$codPedCab'");
    
            echo json_encode($actualizar_pedido->fetch(PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
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