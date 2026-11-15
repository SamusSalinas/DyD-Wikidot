<?php
require_once __DIR__ . '/../../includes/conexion.php';

// Tomamos el ID del trasfondo desde la URL (por defecto el trasfondo 1: Acólito)
$id = isset($_GET['id']) ? (int)$_GET['id'] : 1;

$sql = "SELECT * FROM trasfondos WHERE id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows > 0):
    $trasfondo = $resultado->fetch_assoc();
    // Separamos las competencias por coma para armar una lista prolija
    $lista_habilidades = explode(',', $trasfondo['competencias_habilidades']);
?>

    <h2><?php echo htmlspecialchars($trasfondo['nombre']); ?></h2>

    <h3>Resumen del trasfondo</h3>
    <table>
        <caption>Características principales</caption>
        <tbody>
            <tr>
                <th scope="row">Rasgo especial</th>
                <td><?php echo htmlspecialchars($trasfondo['rasgo']); ?></td>
            </tr>
        </tbody>
    </table>

    <h3>Competencias en habilidades</h3>
    <ul>
        <?php foreach ($lista_habilidades as $habilidad): ?>
            <li><?php echo htmlspecialchars(trim(rtrim($habilidad, '.'))); ?></li>
        <?php endforeach; ?>
    </ul>

    <p><a href="index.php?page=trasfondos/inicio">Volver a todos los trasfondos</a></p>

<?php else: ?>
    <h2>Trasfondo no encontrado</h2>
    <p>No existe un trasfondo con ese identificador en la base de datos.</p>
    <p><a href="index.php?page=trasfondos/inicio">Volver a todos los trasfondos</a></p>
<?php 
endif; 
$stmt->close();
?>