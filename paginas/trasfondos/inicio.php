<?php
require_once __DIR__ . '/../../includes/conexion.php';

$sql = "SELECT id, nombre, competencias_habilidades FROM trasfondos ORDER BY id ASC";
$resultado = $conexion->query($sql);
?>

<h2>Trasfondos disponibles</h2>
<p>Seleccioná un trasfondo para consultar sus competencias en habilidades y su rasgo especial:</p>

<ul>
    <?php while ($fila = $resultado->fetch_assoc()): ?>
        <li>
            <a href="index.php?page=trasfondos/ver_trasfondo&id=<?php echo $fila['id']; ?>"><?php echo htmlspecialchars($fila['nombre']); ?></a>
            <span style="margin-left: 15px;">
                (Habilidades: <?php echo htmlspecialchars($fila['competencias_habilidades']); ?>)
            </span>
        </li>
    <?php endwhile; ?>
</ul>