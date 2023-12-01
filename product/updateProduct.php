<?php

// Importando variable $con que contiene la conexión con MySQL
require_once "../connection.php";

$nombreProducto = limpiar_cadena($_POST['nombreProducto']);
$marca = limpiar_cadena($_POST['marca']);
$descripcion = limpiar_cadena($_POST['descripcion']);
$precio = limpiar_cadena($_POST['precio']);
$cantidadStock = limpiar_cadena($_POST['cantidadStock']);
$codigoCategoria = $_POST['codigoCategoria'];
$codigoProducto = $_POST['codigoProducto'];

if ($nombreProducto == "" || $marca == "" || $descripcion == "" || $precio == "" || $cantidadStock == "") {
    echo json_encode(['error' => ['message' => 'Campos obligatorios faltantes']]);
    exit();
}

// Verificar si se subió una nueva imagen
$imagenSubida = !empty($_FILES['imagen']['tmp_name']);

// Ruta donde se guardarán las imágenes
$directorioDestino = 'images/';

// Obtener la ruta de la imagen anterior
$obtenerRutaImagenAnterior = $con->prepare("SELECT imagen FROM PRODUCTO WHERE codigoProducto = ?");
$obtenerRutaImagenAnterior->bindParam(1, $codigoProducto, PDO::PARAM_INT);
$obtenerRutaImagenAnterior->execute();

if ($obtenerRutaImagenAnterior->rowCount() > 0) {
    $rutaImagenAnterior = $obtenerRutaImagenAnterior->fetchColumn();

    // Eliminar la imagen anterior solo si se subió una nueva imagen
    if ($imagenSubida && file_exists($rutaImagenAnterior)) {
        unlink($rutaImagenAnterior);
    }
}

if ($imagenSubida) {
    // Nombre del archivo
    $nombreArchivo = renombrar_fotos($_FILES['imagen']['name']);

    // Ruta completa del archivo
    $rutaCompleta = $directorioDestino . $nombreArchivo;

    // Mover la nueva imagen al directorio destino
    if (move_uploaded_file($_FILES['imagen']['tmp_name'], $rutaCompleta)) {
        // Actualizar los detalles del producto con la nueva imagen
        $actualizarProducto = $con->prepare("UPDATE PRODUCTO SET nombreProducto = :nombreProducto, descripcion = :descripcion, precio = :precio, cantidadStock = :cantidadStock, imagen = :imagen, marca = :marca, codigoCategoria = :codigoCategoria WHERE codigoProducto = :codigoProducto");

        $marcadores = [
            ":nombreProducto" => $nombreProducto,
            ":descripcion" => $descripcion,
            ":precio" => $precio,
            ":cantidadStock" => $cantidadStock,
            ":imagen" => $rutaCompleta,
            ":marca" => $marca,
            ":codigoCategoria" => $codigoCategoria,
            ":codigoProducto" => $codigoProducto,
        ];

        $actualizarProducto->execute($marcadores);

        if ($actualizarProducto->rowCount() == 1) {
            $nuevaConsulta = $con->query("SELECT * FROM PRODUCTO WHERE codigoProducto = $codigoProducto");
            $productoActualizado = $nuevaConsulta->fetch(PDO::FETCH_ASSOC);

            echo json_encode($productoActualizado, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
        } else {
            echo json_encode(['error' => ['message' => 'Error al actualizar producto']]);
        }
    } else {
        echo json_encode(['error' => ['message' => 'Imagen no pudo ser registrada']]);
    }
} else {
    // No se ha subido una nueva imagen, mantener la ruta actual
    try {
        // Actualizar los detalles del producto sin cambiar la imagen
        $actualizarProducto = $con->prepare("UPDATE PRODUCTO SET nombreProducto = :nombreProducto, descripcion = :descripcion, precio = :precio, cantidadStock = :cantidadStock, marca = :marca, codigoCategoria = :codigoCategoria WHERE codigoProducto = :codigoProducto");

        $marcadores = [
            ":nombreProducto" => $nombreProducto,
            ":descripcion" => $descripcion,
            ":precio" => $precio,
            ":cantidadStock" => $cantidadStock,
            ":marca" => $marca,
            ":codigoCategoria" => $codigoCategoria,
            ":codigoProducto" => $codigoProducto,
        ];

        $actualizarProducto->execute($marcadores);

        if ($actualizarProducto->rowCount() == 1) {
            $nuevaConsulta = $con->query("SELECT * FROM PRODUCTO WHERE codigoProducto = $codigoProducto");
            $productoActualizado = $nuevaConsulta->fetch(PDO::FETCH_ASSOC);

            echo json_encode($productoActualizado, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
        } else {
            echo json_encode(['error' => ['message' => 'No se ha cambiado ningun campo']]);
        }
    } catch (PDOException $e) {
        echo json_encode(['error' => ['message' => 'Error en la base de datos']]);
    }
}
