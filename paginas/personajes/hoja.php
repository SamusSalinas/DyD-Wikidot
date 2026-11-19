<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../includes/conexion.php';
require_once __DIR__ . '/../../includes/funciones_personaje.php';

// Inicializar el array del personaje en sesión si no existe
if (!isset($_SESSION['personaje'])) {
    $_SESSION['personaje'] = [
        'nombre' => '',
        'jugador' => '',
        'raza' => '',
        'subraza'=> '',
        'clase' => '',
        'subclase' => '',
        'trasfondo' => '',
        'alineamiento' => '',
        'nivel' => 1,
        'experiencia' => 0,
        'atributos' => ['str' => 10, 'dex' => 10, 'con' => 10, 'int' => 10, 'wis' => 10, 'cha' => 10],
        'salvaciones' => [],
        'habilidades' => [],
        'equipo' => '',
        'rasgos' => ''
    ];
}

// Si se mandó información por POST (al hacer clic en Siguiente o cambiar clase), la guardamos en la sesión
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST as $clave => $valor) {
        $_SESSION['personaje'][$clave] = $valor;
    }
}

// Definir en qué capítulo estamos usando 'capitulo' de forma unificada (por defecto el 1)
$capitulo = isset($_GET['capitulo']) ? (int)$_GET['capitulo'] : 1;

// Cargar catálogos necesarios para los selects (clases, razas, trasfondos)
try {
    $pdo = conexion_bd();
    $clases = $pdo->query('SELECT nombre FROM clases ORDER BY id')->fetchAll(PDO::FETCH_COLUMN);
    $razas = $pdo->query('SELECT name FROM razas ORDER BY id')->fetchAll(PDO::FETCH_COLUMN);
    $trasfondos = $pdo->query('SELECT name FROM trasfondos ORDER BY name')->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $error) {
    $clases = []; $razas = []; $trasfondos = [];
}

// Pasamos los datos actuales de la sesión a una variable corta para usar en las vistas
$personaje = $_SESSION['personaje'];
$atributos_def = personaje_atributos();
$habilidades_def = personaje_habilidades();
?>

<div>
    <?php
    // Selector dinámico de capítulos
    switch ($capitulo) {
        case 1:
            include __DIR__ . '/identidad.php';
            break;
        case 2:
            include __DIR__ . '/atributos_y_combate.php';
            break;
        case 3:
            include __DIR__ . '/habilidades.php';
            break;
        case 4:
            include __DIR__ . '/equipo_y_notas.php';
            break;
        default:
            include __DIR__ . '/identidad.php';
            break;
    }
    ?>
</div>