<?php
function conexion_configuracion()
{
    $host = getenv('DB_HOST');
    $nombre = getenv('DB_NAME');
    $usuario = getenv('DB_USER');
    $contrasena = getenv('DB_PASSWORD');

    return array(
        'host' => $host !== false && $host !== '' ? $host : '127.0.0.1',
        'nombre' => $nombre !== false && $nombre !== '' ? $nombre : 'dnd_wikidot',
        'usuario' => $usuario !== false && $usuario !== '' ? $usuario : 'root',
        'contrasena' => $contrasena !== false ? $contrasena : ''
    );
}

function conexion_bd()
{
    static $conexion = null;
    if ($conexion instanceof PDO) {
        return $conexion;
    }

    $configuracion = conexion_configuracion();
    $dsn = 'mysql:host=' . $configuracion['host'] . ';dbname=' . $configuracion['nombre'] . ';charset=utf8mb4';
    $conexion = new PDO($dsn, $configuracion['usuario'], $configuracion['contrasena'], array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ));

    return $conexion;
}

function conexion_mysqli()
{
    static $conexion = null;
    if ($conexion instanceof mysqli) {
        return $conexion;
    }

    $configuracion = conexion_configuracion();
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $conexion = new mysqli(
        $configuracion['host'],
        $configuracion['usuario'],
        $configuracion['contrasena'],
        $configuracion['nombre']
    );
    $conexion->set_charset('utf8mb4');

    return $conexion;
}

$conexion = conexion_mysqli();
