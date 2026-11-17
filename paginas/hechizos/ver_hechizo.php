<?php
require_once __DIR__ . '/../../includes/conexion.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 1;

$sql = "SELECT * FROM hechizos WHERE id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows > 0):
    $hechizo = $resultado->fetch_assoc();
?>

    <h2><?php echo htmlspecialchars($hechizo['nombre']); ?></h2>
    <p><a href="javascript:history.back()">← Volver atrás</a></p>
    <hr>

    <table>
        <tbody>
            <tr>
                <th scope="row">Nivel</th>
                <td><?php echo ($hechizo['nivel'] == 0) ? 'Truco / Nivel 0' : 'Nivel ' . (int)$hechizo['nivel']; ?></td>
            </tr>
            <tr>
                <th scope="row">Escuela</th>
                <td><?php echo htmlspecialchars($hechizo['escuela']); ?></td>
            </tr>
            <tr>
                <th scope="row">Clases permitidas</th>
                <td><?php echo htmlspecialchars($hechizo['clases_permitidas']); ?></td>
            </tr>
        </tbody>
    </table>

    <h3>Descripción</h3>
    <div style="white-space: pre-line; background: #f9f9f9; padding: 15px; border-radius: 4px; border: 1px solid #ddd; color: #333;">
        <?php echo htmlspecialchars($hechizo['descripcion']); ?>
    </div>

<?php else: ?>
    <h2>Hechizo no encontrado</h2>
    <p>No existe un hechizo con ese identificador en la base de datos.</p>
    <p><a href="index.php?page=hechizos/inicio">Volver al inicio de hechizos</a></p>
<?php
endif;
$stmt->close();
?>