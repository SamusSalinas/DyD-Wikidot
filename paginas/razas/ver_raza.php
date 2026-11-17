<?php
require_once __DIR__ . '/../../includes/conexion.php';

// Tomamos el ID de la raza desde la URL (por defecto la raza 1: Humano)
$id = isset($_GET['id']) ? (int)$_GET['id'] : 1;

$sql = "SELECT * FROM razas WHERE id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows > 0):
    $raza = $resultado->fetch_assoc();
    // Separamos los rasgos por coma para listarlos de forma ordenada
    $lista_rasgos = explode(',', $raza['rasgos_principales']);
?>

    <h2><?php echo htmlspecialchars($raza['nombre']); ?></h2>

    <h3>Resumen de la raza</h3>
    <table>
        <caption>Características principales</caption>
        <tbody>
            <tr>
                <th scope="row">Bonificador de características</th>
                <td><?php echo htmlspecialchars($raza['bono_estadisticas']); ?></td>
            </tr>
            <tr>
                <th scope="row">Velocidad base</th>
                <td><?php echo htmlspecialchars($raza['velocidad']); ?> pies</td>
            </tr>
        </tbody>
    </table>

    <h3>Rasgos raciales</h3>
    <ul>
        <?php foreach ($lista_rasgos as $rasgo): ?>
            <li><?php echo htmlspecialchars(trim(rtrim($rasgo, '.'))); ?></li>
        <?php endforeach; ?>
    </ul>

    <p><a href="index.php?page=razas/inicio">Volver a todas las razas</a></p>

<?php else: ?>
    <h2>Raza no encontrada</h2>
    <p>No existe una raza con ese identificador en la base de datos.</p>
    <p><a href="index.php?page=razas/inicio">Volver a todas las razas</a></p>
<?php
endif;
$stmt->close();
?>