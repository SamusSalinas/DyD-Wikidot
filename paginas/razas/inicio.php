<?php
require_once __DIR__ . '/../../includes/conexion.php';

$sql = "SELECT id, nombre, bono_estadisticas, velocidad FROM razas ORDER BY id ASC";
$resultado = $conexion->query($sql);
?>

<h2>Razas disponibles</h2>
<p>Seleccioná una raza para consultar sus atributos, velocidad y rasgos principales:</p>

<ul>
    <?php while ($fila = $resultado->fetch_assoc()): ?>
        <li>
            <a href="index.php?page=razas/ver_raza&id=<?php echo $fila['id']; ?>"><?php echo htmlspecialchars($fila['nombre']); ?></a>
            <span style="margin-left: 15px;">
                (<?php echo htmlspecialchars($fila['bono_estadisticas']); ?> | Velocidad: <?php echo htmlspecialchars($fila['velocidad']); ?> pies)
            </span>
        </li>
    <?php endwhile; ?>
</ul>