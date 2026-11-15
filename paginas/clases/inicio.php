<?php
require_once __DIR__ . '/../../includes/conexion.php';

$sql = "SELECT id, nombre, dado_golpe, caracteristica_principal FROM clases ORDER BY id ASC";
$resultado = $conexion->query($sql);
?>

<h2>Clases disponibles</h2>
<p>Seleccioná una clase para consultar sus estadísticas y equipo inicial:</p>

<ul>
    <?php while ($fila = $resultado->fetch_assoc()): ?>
        <li>
            <a href="index.php?page=clases/ver_clase&id=<?php echo $fila['id']; ?>"><?php echo htmlspecialchars($fila['nombre']); ?></a>
            <span style="margin-left: 20px;">
                (Dado: <?php echo htmlspecialchars($fila['dado_golpe']); ?> | Principal: <?php echo htmlspecialchars($fila['caracteristica_principal']); ?>)
            </span>
        </li>
    <?php endwhile; ?>
</ul>