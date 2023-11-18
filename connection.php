<?php

$dsn = "mysql:host=localhost;dbname=app_medica";
$user = "root";
$pass = "ifbva2002";
$option = array(
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES UTF8", //Para aceptar caracteres espanoles
);

try {
    $con = new PDO($dsn, $user, $pass, $option);
    $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    echo json_encode(['error' => ['message' => 'Error de conexion']]);
    die();
}

// Verificar datos
function verificar_datos($filtro, $cadena)
{
    // Si coincide se devuelve falso y no pasa nada
    if (preg_match("/^" . $filtro . "$/", $cadena)) {
        return false;
    } else {
        return true;
    }
}

// Limpiar cadenas de texto
function limpiar_cadena($cadena)
{
    $patrones = [
        '/\<script\>/i',
        '/\<\/script\>/i',
        '/\<script src\>/i',
        '/\<script type=\>/i',
        '/SELECT \* FROM/i',
        '/DELETE FROM/i',
        '/INSERT INTO/i',
        '/DROP TABLE/i',
        '/DROP DATABASE/i',
        '/TRUNCATE TABLE/i',
        '/SHOW TABLES/i',
        '/SHOW DATABASES/i',
        '/\<\?php/i',
        '/\?>/i',
        '/--/i',
        // '/\^/i',
        // '/\</i',
        // '/\[/i',
        // '/\]/i',
        '/==/i',
        // '/\;/i',
        '/\:\:/i',

    ];
    $cadena = trim($cadena);
    $cadena = stripslashes($cadena);
    $string = preg_replace($patrones, '', $cadena);
    $cadena = trim($cadena);
    $cadena = stripslashes($cadena);
    return $string;

}

// Renombrar fotos
function renombrar_fotos($nombre)
{
    $nombre = str_ireplace(" ", "_", $nombre);
    $nombre = str_ireplace("/", "_", $nombre);
    $nombre = str_ireplace("#", "_", $nombre);
    $nombre = str_ireplace("-", "_", $nombre);
    $nombre = str_ireplace("$", "_", $nombre);
    // $nombre=str_ireplace(".","_",$nombre);
    $nombre = str_ireplace(",", "_", $nombre);
    // $nombre=$nombre."_".rand(0,100);
    return $nombre;
}
