<?php
require_once __DIR__ . '/../../includes/conexion.php';

// Tomamos el ID del dote desde la URL (por defecto el 1: Alerta)
$id = isset($_GET['id']) ? (int)$_GET['id'] : 1;

$sql = "SELECT * FROM dotes WHERE id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows > 0):
    $dote = $resultado->fetch_assoc();
    // Separamos los beneficios por coma para armar los ítems de la lista
    $lista_beneficios = explode(',', $dote['beneficios']);
?>

    <h2><?php echo htmlspecialchars($dote['nombre']); ?></h2>

    <h3>Resumen del dote</h3>
    <table>
        <caption>Requisitos</caption>
        <tbody>
            <tr>
                <th scope="row">Prerrequisito</th>
                <td><?php echo htmlspecialchars($dote['prerequisito']); ?></td>
            </tr>
        </tbody>
    </table>

    <h3>Beneficios</h3>
    <ul>
        <?php foreach ($lista_beneficios as $beneficio): ?>
            <li><?php echo htmlspecialchars(trim(rtrim($beneficio, '.'))); ?></li>
        <?php endforeach; ?>
    </ul>

    <p><a href="index.php?page=dotes/inicio">Volver a todos los dotes</a></p>

<?php else: ?>
    <h2>Dote no encontrado</h2>
    <p>No existe un dote con ese identificador en la base de datos.</p>
    <p><a href="index.php?page=dotes/inicio">Volver a todos los dotes</a></p>
<?php
endif;
$stmt->close();
?>