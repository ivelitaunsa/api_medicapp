<?php

// Por defecto devuelve fechas en ingles
date_default_timezone_set("America/Lima");

require_once "../connection.php";

$codigoUsuario = $_POST['codigoUsuario'];
$precioTotal = $_POST['precioTotal'];
$direccionEntrega = $_POST['direccionEntrega'];
$direccionReferencia = $_POST['direccionReferencia'];

$rutaCompleta = '';
$esConReceta = 0;
$codEstEnt = 0;

if($codigoUsuario==null || $precioTotal==null || $direccionEntrega ==null || $direccionReferencia ==null) {
    echo json_encode(['error' => ['message' => 'Campos Obligatorios Faltantes']]);
    exit();
}

if($_FILES != null) {
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
    $check_ruta_imagen = $con->query("SELECT * FROM PEDIDO_CABECERA WHERE fotoReceta='$rutaCompleta'");
    if ($check_ruta_imagen->rowCount() > 0) {
        echo json_encode(['error' => ['message' => 'Nombre de imagen ya registrada']]);
        exit();
    }
    // Mover la imagen al directorio destino
    if (!move_uploaded_file($_FILES['imagen']['tmp_name'], $rutaCompleta)) {
        echo json_encode(['error' => ['message' => 'Imagen no pudo ser registrada']]);
        exit();
    }
    $esConReceta = 1;
}

try {
    $codPedCab = $con->query("SELECT * FROM PEDIDO_CABECERA");
    $codPedCab = $codPedCab->rowCount() + 101;
} catch (PDOException $e) {
    echo json_encode(['error' => ['message' => 'Error en la base de datos al consultar entradas']]);
    exit();
}

// Aquí puedes realizar acciones adicionales, como almacenar los datos en una base de datos.
try {

    // Preparando consulta: Mas seguro que el query
    $guardar_pedido = $con->prepare("INSERT INTO PEDIDO_CABECERA(codPedCab,codigoUsuario,fecha,precioTotal,esConReceta,fotoReceta,direccionEntrega,direccionReferencia,codEstEnt) VALUES(:codPedCab,:codigoUsuario,:fecha,:precioTotal,:esConReceta,:fotoReceta,:direccionEntrega,:direccionReferencia,:codEstEnt)");
    // Al registrarse el estado es autenticado
    $marcadores = [
        "codPedCab"=> $codPedCab,
        "codigoUsuario"=> $codigoUsuario,
        "fecha"=> date("Y-m-d"),
        "precioTotal"=> $precioTotal,
        "esConReceta"=> $esConReceta,
        "fotoReceta"=> $rutaCompleta,
        "direccionEntrega"=> $direccionEntrega,
        "direccionReferencia"=> $direccionReferencia,
        "codEstEnt"=> $codEstEnt,
    ];

    // Después de realizar el INSERT
    if ($guardar_pedido->execute($marcadores)) {
        $nuevaConsulta = $con->query("SELECT * FROM PEDIDO_CABECERA WHERE codPedCab='$codPedCab'");
        $nuevoPedido = $nuevaConsulta->fetch(PDO::FETCH_ASSOC);

        echo json_encode($nuevoPedido, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
    } else {
        echo json_encode(['error' => ['message' => 'Error al guardar producto']]);
    }

} catch (PDOException $e) {
    echo json_encode(['error' => ['message' => 'Error en la base de datos']]);
}