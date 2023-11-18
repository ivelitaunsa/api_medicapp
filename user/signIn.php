<?php

// Importanto variable $con que contiene la conexion con mysql
require_once "../connection.php";

$nombreUsuario = limpiar_cadena($_POST['nombreUsuario']);
$correo = limpiar_cadena($_POST['correo']);
$contrasena = limpiar_cadena($_POST['contrasena']);
$es_correo = 0;

// Verifica si es un correo electrónico
if (!empty($correo)) {
    // Verificar correo
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['error' => ['message' => 'Formato de correo incorrecto']]);
        exit();
    }
    $es_correo = 1;
} elseif (!empty($nombreUsuario)) {
    // Verificar formato de usuario
    if (verificar_datos("[a-zA-Z0-9]{4,255}", $nombreUsuario)) {
        echo json_encode(['error' => ['message' => 'Formato de usuario incorrecto']]);
        exit();
    }
} else {
    // Manejo de errores u otra lógica
    echo json_encode(['error' => ['message' => 'Campos obligatorios faltantes']]);
    exit();
}

// Verificar campos obligatorios
if (($es_correo && empty($correo)) || (!$es_correo && empty($nombreUsuario)) || empty($contrasena)) {
    echo json_encode(['error' => ['message' => 'Campos obligatorios faltantes']]);
    exit();
}

// Verificar formato de contrasena
if (verificar_datos("[a-zA-Z0-9!@#$%&/()=+?[]~-^]{4,100}", $contrasena)) {
    echo json_encode(['error' => ['message' => 'Formato de contraseña incorrecto']]);
    exit();
}

// Verificar nombre de usuario o correo desde la base de datos
try {
    // Con prepare tambien se evita inyeccion sql
    $stmt = $con->prepare($es_correo ? "SELECT * FROM USUARIO WHERE correo = :correo" : "SELECT * FROM USUARIO WHERE nombreUsuario = :nombreUsuario");
    $stmt->bindParam(':correo', $correo);
    $stmt->bindParam(':nombreUsuario', $nombreUsuario);
    $stmt->execute();

    if ($stmt->rowCount() == 1) {
        $check_usuario = $stmt->fetch();
        if (password_verify($contrasena, $check_usuario['contrasena'])) {
            // Actualizando el estado de autenticación
            $updateStmt = $con->prepare("UPDATE USUARIO SET esAutenticado = 1 WHERE codigoUsuario = :codigoUsuario");
            $updateStmt->bindParam(':codigoUsuario', $check_usuario['codigoUsuario']);
            $updateStmt->execute();

            // Verificando si la actualización fue exitosa
            if ($updateStmt->rowCount() > 0) {
                // Actualizando el valor de esAutenticado en el array antes de enviar la respuesta
                $check_usuario['esAutenticado'] = 1;
                echo json_encode($check_usuario->fetch(PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
            } else {
                echo json_encode(['error' => ['message' => 'Error al actualizar el estado de autenticación']]);
            }
        } else {
            echo json_encode(['error' => ['message' => 'Usuario o clave incorrectos']]);
        }
    } else {
        echo json_encode(['error' => ['message' => 'Usuario o clave incorrectos']]);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => ['message' => 'Error en la base de datos']]);
} finally {
    $stmt = null;
    $updateStmt = null;
    $con = null;
    $check_usuario=null;
}
