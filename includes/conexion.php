<?php
$host = 'localhost';
$usuario = 'root';
$password = ''; // En XAMPP viene vacío por defecto
$base_datos = 'dnd_wikidot';

$conexion = new mysqli($host, $usuario, $password, $base_datos);

// Verificar si hubo algún error al conectar
if ($conexion->connect_error) {
    die("Error de conexión a la base de datos: " . $conexion->connect_error);
}

$conexion->set_charset("utf8mb4");
?>