<?php
require_once __DIR__ . '/../../includes/conexion.php';

$slug = isset($_GET['clase']) ? trim($_GET['clase']) : 'wizard';

// Mapeo del slug de la URL al nombre que guardaste en la columna 'clases_permitidas' de tu BD
$mapa_clases = [
    'bard' => 'Bardo',
    'cleric' => 'Clérigo',
    'druid' => 'Druida',
    'paladin' => 'Paladín',
    'ranger' => 'Explorador',
    'sorcerer' => 'Hechicero',
    'warlock' => 'Brujo',
    'wizard' => 'Mago'
];

$nombre_clase_espanol = isset($mapa_clases[$slug]) ? $mapa_clases[$slug] : 'Mago';
$nombre_clase_ingles = ucfirst($slug);

// Consultamos los hechizos que contengan esta clase en la columna 'clases_permitidas'
$sql = "SELECT id, nombre FROM hechizos WHERE clases_permitidas LIKE ? ORDER BY nivel ASC, nombre ASC";
$stmt = $conexion->prepare($sql);
$param_like = "%" . $nombre_clase_espanol . "%";
$stmt->bind_param("s", $param_like);
$stmt->execute();
$resultado = $stmt->get_result();
?>

<h2>Hechizos de <?php echo htmlspecialchars($nombre_clase_ingles); ?></h2>
<p><a href="index.php?page=hechizos/inicio">← Volver a Todas las Clases Mágicas</a></p>
<hr>

<?php if ($resultado->num_rows > 0): ?>
    <ul>
        <?php while ($hechizo = $resultado->fetch_assoc()): ?>
            <li>
                <a href="index.php?page=hechizos/ver_hechizo&id=<?php echo $hechizo['id']; ?>">
                    <?php echo htmlspecialchars($hechizo['nombre']); ?>
                </a>
            </li>
        <?php endwhile; ?>
    </ul>
<?php else: ?>
    <p>No se encontraron hechizos registrados para esta clase en la base de datos local.</p>
<?php endif; ?>

<?php
$stmt->close();
?>