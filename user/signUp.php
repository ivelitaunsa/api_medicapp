<?php

// Importanto variable $con que contiene la conexion con mysql
require_once "../connection.php";

$nombreUsuario = limpiar_cadena($_POST['nombreUsuario']);
$correo = limpiar_cadena($_POST['correo']);
$contrasena = limpiar_cadena($_POST['contrasena']);

// Verificar campos obligatorios
if ($nombreUsuario == "" || $correo == "" || $contrasena == "") {
    echo json_encode(['error' => ['message' => 'Campos obligatorios faltantes']]);
    exit();
}

// Verificar formato de usuario
if (verificar_datos("[a-zA-Z0-9]{4,255}", $nombreUsuario)) {
    echo json_encode(['error' => ['message' => 'Formato de usuario incorrecto']]);
    exit();
}

// Verificar formato de contrasena
if (verificar_datos("[a-zA-Z0-9!@#$%&/()=+?[]~-^]{4,100}", $contrasena)) {
    echo json_encode(['error' => ['message' => 'Formato de contraseña incorrecto']]);
    exit();
}

// Verificar correo
if ($correo != "") {
    if (filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $check_correo = $con->query("SELECT * FROM USUARIO WHERE correo='$correo'");
        if ($check_correo->rowCount() > 0) {
            echo json_encode(['error' => ['message' => 'Correo ya registrado']]);
            exit();
        }
        // Cerrando conexion
        $check_correo = null;
    } else {
        echo json_encode(['error' => ['message' => 'Formato de correo incorrecto']]);
        exit();
    }
}

// Verificar nombre de usuario
$check_usuario = $con->query("SELECT nombreUsuario FROM USUARIO WHERE nombreUsuario='$nombreUsuario'");
if ($check_usuario->rowCount() > 0) {
    echo json_encode(['error' => ['message' => 'Nombre de usuario ya registrado']]);
    exit();
}
$check_usuario = null;

// Generando contrasena encriptada

$encriptacion = password_hash($contrasena, PASSWORD_BCRYPT, ["cost" => 10]);

// Guardando datos
try {

    // Preparando consulta: Mas seguro que el query
    $guardar_usuario = $con->prepare("INSERT INTO USUARIO(nombreUsuario,contrasena,correo,esAutenticado) VALUES(:nombreUsuario,:encriptacion,:correo,:esAutenticado)");
    // Al registrarse el estado es autenticado
    $marcadores = [
        ":nombreUsuario" => $nombreUsuario,
        ":encriptacion" => $encriptacion,
        ":correo" => $correo,
        ":esAutenticado" => "1",
    ];
    $guardar_usuario->execute($marcadores);

    if ($guardar_usuario->rowCount() == 1) {
        echo json_encode($guardar_usuario, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
    } else {
        echo json_encode(['error' => ['message' => 'Error al guardar usuario']]);
    }

} catch (PDOException $e) {
    echo json_encode(['error' => ['message' => 'Error en la base de datos']]);
} finally {
    $guardar_usuario=null;
    $con=null;
}