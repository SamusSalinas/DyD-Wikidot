<?php
require_once __DIR__ . '/../../includes/conexion.php';

// Tomamos el ID de la clase desde la URL (si no viene ninguno, mostramos la clase 1 por defecto)
$id = isset($_GET['id']) ? (int)$_GET['id'] : 1;

// 1. Consultamos la información principal de la clase
$sql = "SELECT * FROM clases WHERE id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows > 0):
    $clase = $resultado->fetch_assoc();
    // Separamos el texto del equipo inicial por comas para armar una lista prolija
    $lista_equipo = explode(',', $clase['equipo_inicial']);

    // 2. Consultamos las subclases asociadas a esta clase en la base de datos
    $sql_sub = "SELECT * FROM subclases WHERE clase_id = ?";
    $stmt_sub = $conexion->prepare($sql_sub);
    $stmt_sub->bind_param("i", $id);
    $stmt_sub->execute();
    $resultado_sub = $stmt_sub->get_result();
?>

    <h2><?php echo htmlspecialchars($clase['nombre']); ?></h2>

    <h3>Resumen de la clase</h3>
    <table>
        <caption>Características principales</caption>
        <tbody>
            <tr>
                <th scope="row">Atributo principal</th>
                <td><?php echo htmlspecialchars($clase['caracteristica_principal']); ?></td>
            </tr>
            <tr>
                <th scope="row">Dado de golpe</th>
                <td><?php echo htmlspecialchars($clase['dado_golpe']); ?></td>
            </tr>
            <tr>
                <th scope="row">Tiradas de salvación</th>
                <td><?php echo htmlspecialchars($clase['salvaciones']); ?></td>
            </tr>
        </tbody>
    </table>

    <h3>Equipo inicial</h3>
    <ul>
        <?php foreach ($lista_equipo as $item): ?>
            <li><?php echo htmlspecialchars(trim(rtrim($item, '.'))); ?></li>
        <?php endforeach; ?>
    </ul>

    <h3>Subclases disponibles</h3>
    <ul>
        <?php if ($resultado_sub->num_rows > 0): ?>
            <?php while ($sub = $resultado_sub->fetch_assoc()): ?>
                <li>
                    <strong><?php echo htmlspecialchars($sub['nombre']); ?>:</strong> 
                    <?php echo htmlspecialchars($sub['descripcion']); ?>
                </li>
            <?php endwhile; ?>
        <?php else: ?>
            <li>No hay subclases registradas para esta clase todavía.</li>
        <?php endif; ?>
    </ul>

    <p><a href="index.php?page=clases/inicio">Volver a todas las clases</a></p>

<?php else: ?>
    <h2>Clase no encontrada</h2>
    <p>No existe una clase con ese identificador en la base de datos.</p>
    <p><a href="index.php?page=clases/inicio">Volver a todas las clases</a></p>
<?php 
endif; 

// Cerramos las sentencias preparadas
$stmt->close();
if (isset($stmt_sub)) {
    $stmt_sub->close();
}
?>