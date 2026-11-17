<?php
require_once __DIR__ . '/../../includes/conexion.php';

$sql = "SELECT id, nombre, prerequisito FROM dotes ORDER BY id ASC";
$resultado = $conexion->query($sql);
?>

<h2>Dotes disponibles</h2>
<p>Seleccioná un dote para consultar sus prerrequisitos y beneficios:</p>

<ul>
    <?php while ($fila = $resultado->fetch_assoc()): ?>
        <li>
            <a href="index.php?page=dotes/ver_dote&id=<?php echo $fila['id']; ?>"><?php echo htmlspecialchars($fila['nombre']); ?></a>
            <span style="margin-left: 15px;">
                (Prerrequisito: <?php echo htmlspecialchars($fila['prerequisito']); ?>)
            </span>
        </li>
    <?php endwhile; ?>
</ul>