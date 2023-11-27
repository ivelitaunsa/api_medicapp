<?php

// Importanto variable $con que contiene la conexion con mysql
require_once "../connection.php";

$nombreProducto = limpiar_cadena($_POST['nombreProducto']);
$marca = limpiar_cadena($_POST['marca']);
$descripcion = limpiar_cadena($_POST['descripcion']);
$precio = limpiar_cadena($_POST['precio']);
$cantidadStock = limpiar_cadena($_POST['cantidadStock']);
$codigoCategoria = $_POST['codigoCategoria'];

if($nombreProducto=="" || $marca=="" || $descripcion=="" || $precio=="" || $cantidadStock=="") {
    echo json_encode(['error' => ['message' => 'Campos obligatorios faltantes']]);
    exit();
}

if($_FILES == null) {
    echo json_encode(['error' => ['message' => 'No ha subido una imagen']]);
    exit();
}

if (mime_content_type($_FILES['imagen']['tmp_name'])!="image/jpeg" && mime_content_type($_FILES['fichero']['tmp_name'])!="image/png") {
    echo json_encode(['error' => ['message' => 'Tipo de archivos no soportado']]);
    exit();
}

if (!file_exists("images")) {
    if(!mkdir("images",0777)) {
        echo json_encode(['error' => ['message' => 'Error al crear el directorio']]);
        exit();
    }
}

// Dando permisos al directorio para modificarlo
chmod("images",0777);

// Directorio donde se guardarán las imágenes
$directorioDestino = 'images/';
 
// Nombre del archivo
$nombreArchivo = renombrar_fotos($_FILES['imagen']['name']);

// Ruta completa del archivo
$rutaCompleta = $directorioDestino . $nombreArchivo;

// Verificar ruta de imagen
$check_ruta_imagen = $con->query("SELECT * FROM PRODUCTO WHERE imagen='$rutaCompleta'");
if ($check_ruta_imagen->rowCount() > 0) {
    echo json_encode(['error' => ['message' => 'Nombre de imagen ya registrada']]);
    exit();
}

// Mover la imagen al directorio destino
if (move_uploaded_file($_FILES['imagen']['tmp_name'], $rutaCompleta)) {
    // Aquí puedes realizar acciones adicionales, como almacenar los datos en una base de datos.
    try {

        // Preparando consulta: Mas seguro que el query
        $guardar_producto = $con->prepare("INSERT INTO PRODUCTO(nombreProducto,descripcion,precio,cantidadStock,imagen,marca,codigoCategoria) VALUES(:nombreProducto,:descripcion,:precio,:cantidadStock,:imagen,:marca,:codigoCategoria)");
        // Al registrarse el estado es autenticado
        $marcadores = [
            ":nombreProducto" => $nombreProducto,
            ":descripcion" => $descripcion,
            ":precio" => $precio,
            ":cantidadStock" => $cantidadStock,
            ":imagen" => $rutaCompleta,
            ":marca" => $marca,
            ":codigoCategoria" => $codigoCategoria,
        ];
        $guardar_producto->execute($marcadores);
    
        // Después de realizar el INSERT
        if ($guardar_producto->rowCount() == 1) {
            $nuevaConsulta = $con->query("SELECT * FROM PRODUCTO WHERE imagen='$rutaCompleta'");
            $nuevoProducto = $nuevaConsulta->fetch(PDO::FETCH_ASSOC);
    
            echo json_encode($nuevoProducto, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
        } else {
            echo json_encode(['error' => ['message' => 'Error al guardar producto']]);
        }
    
    } catch (PDOException $e) {
        echo json_encode(['error' => ['message' => 'Error en la base de datos']]);
    }
} else {
    echo json_encode(['error' => ['message' => 'Imagen no pudo ser registrada']]);
}