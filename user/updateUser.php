<?php

// Importanto variable $con que contiene la conexion con mysql
require_once "../connection.php";

// Obteniendo el codigoUsuario
$codigoUsuario=$_POST['codigoUsuario'];

// Verificar el usuario
$check_usuario=$con->query("SELECT * FROM USUARIO WHERE codigoUsuario='$codigoUsuario'");

if($check_usuario->rowCount()<=0){
    echo json_encode(['error' => ['message' => 'No existe el usuario en el sistema']]);
    exit();
}else{
    $datos=$check_usuario->fetch();
}
$check_usuario=null;

$nombre=limpiar_cadena($_POST['nombre']);
$apellido=limpiar_cadena($_POST['apellido']);
$correo=limpiar_cadena($_POST['correo']);
$fechaNacimiento=$_POST['fechaNacimiento'];
$genero=$_POST['genero'];

// Verificar correo
if ($correo != "" && $correo != $datos['correo']) {
    if (filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $check_correo = $con->query("SELECT * FROM USUARIO WHERE correo='$correo'");
        if ($check_correo->rowCount() > 0) {
            echo json_encode(['error' => ['message' => 'Correo ya registrado']]);
            exit();
        }
        // Cerrando conexion
        $check_correo = null;
    } else {
        echo json_encode(['error' => ['message' => 'No cambiaste el correo o el campo esta vacio']]);
        exit();
    }
}

// Verificar fecha
$timestamp = strtotime($fechaNacimiento);

if ($timestamp === false) {
    echo json_encode(['error' => ['message' => 'Formato de fecha incorrecta']]);
    exit();
} else {
    $fechaFormateada = date('Y-m-d', $timestamp);
}

// Actualizar datos
try {
    $actualizar_usuario=$con->prepare("UPDATE USUARIO SET nombre=:nombre,apellido=:apellido,correo=:correo,fechaNacimiento=:fechaNacimiento,genero=:genero WHERE codigoUsuario=:codigoUsuario");
    $marcadores = [
        ":nombre" => $nombre,
        ":apellido" => $apellido,
        ":correo" => $correo,
        ":fechaNacimiento" => $fechaFormateada,
        ":genero" => $genero,
        ":codigoUsuario" => $codigoUsuario,

    ];
    $actualizar_usuario->execute($marcadores);

    if ($actualizar_usuario->rowCount() == 1) {
        $actualizar_usuario = $con->query("SELECT * FROM USUARIO WHERE codigoUsuario = '$codigoUsuario'");

        echo json_encode($actualizar_usuario->fetch(PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
    } else {
        echo json_encode(['error' => ['message' => 'Error al seleccionar registro actualizado']]);
    }
    
} catch (PDOException $e) {
    echo json_encode(['error' => ['message' => 'Error en la base de datos']]);
}
$actualizar_usuario=null;
