<?php

// Importando variable $con que contiene la conexión con MySQL
require_once "../connection.php";

// El dato objetivo
$codigoProducto = $_POST['codigoProducto'];
$esAdmin = $_POST['esAdmin'];

if (!empty($esAdmin) && $esAdmin != 0) {
    try {
        // Obtener la ruta de la imagen desde la base de datos
        $obtenerRutaImagen = $con->prepare("SELECT imagen FROM PRODUCTO WHERE codigoProducto = ?");
        $obtenerRutaImagen->bindParam(1, $codigoProducto, PDO::PARAM_INT);
        $obtenerRutaImagen->execute();

        if ($obtenerRutaImagen->rowCount() > 0) {
            // Obtener la ruta de la imagen
            $rutaImagen = $obtenerRutaImagen->fetchColumn();

            // Eliminar el producto de la base de datos
            $eliminarProducto = $con->prepare("DELETE FROM PRODUCTO WHERE codigoProducto = ?");
            $eliminarProducto->bindParam(1, $codigoProducto, PDO::PARAM_INT);
            $eliminarProducto->execute();

            // Verificar si se eliminó correctamente
            if ($eliminarProducto->rowCount() > 0) {
                // Eliminar la imagen física del servidor
                if (file_exists($rutaImagen)) {
                    unlink($rutaImagen);
                }

                echo json_encode(['status' => 'Producto y imagen eliminados']);
            } else {
                echo json_encode(['error' => ['message' => 'No se encontró el producto']]);
            }
        } else {
            echo json_encode(['error' => ['message' => 'No se encontró la imagen']]);
        }
    } catch (PDOException $e) {
        echo json_encode(['error' => ['message' => 'Error al eliminar el producto']]);
    }
} else {
    echo json_encode(['error' => ['message' => 'No tiene los permisos para hacer esa acción']]);
}

// Liberando recursos
$obtenerRutaImagen = null;
$eliminarProducto = null;
