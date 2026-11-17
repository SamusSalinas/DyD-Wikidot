<?php
function conexion_bd()
{
    static $conexion = null;
    if ($conexion instanceof PDO) {
        return $conexion;
    }

    $host = getenv('DB_HOST');
    $nombre = getenv('DB_NAME');
    $usuario = getenv('DB_USER');
    $contrasena = getenv('DB_PASSWORD');

    $host = $host !== false && $host !== '' ? $host : '127.0.0.1';
    $nombre = $nombre !== false && $nombre !== '' ? $nombre : 'dnd_wikidot';
    $usuario = $usuario !== false && $usuario !== '' ? $usuario : 'root';
    $contrasena = $contrasena !== false ? $contrasena : '';

    $dsn = 'mysql:host=' . $host . ';dbname=' . $nombre . ';charset=utf8mb4';
    $conexion = new PDO($dsn, $usuario, $contrasena, array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ));

    return $conexion;
}
