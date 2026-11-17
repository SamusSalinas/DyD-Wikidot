<?php
require_once __DIR__ . '/../../includes/conexion.php';

try {
    $objetos = conexion_bd()->query(
        'SELECT slug, nombre, categoria, coste, peso, dano, clase_armadura, propiedades, descripcion
         FROM objetos_equipo
         ORDER BY nombre ASC'
    )->fetchAll();
} catch (PDOException $error) {
    error_log('Error al cargar equipamiento: ' . $error->getMessage());
    http_response_code(500);
    echo '<h2>Error al cargar el equipamiento</h2>';
    echo '<p>No se pudo consultar el catálogo de objetos en la base de datos.</p>';
    return;
}
?>
<h2>Equipamiento y objetos</h2>
<p>Selecciona un objeto para desplegar su ficha completa.</p>

<?php if (count($objetos) === 0) { ?>
    <p role="status">No hay objetos en el catálogo.</p>
<?php } ?>

<ul class="lista-objetos">
    <?php foreach ($objetos as $objeto) { ?>
        <li>
            <details class="objeto-detalle">
                <summary class="objeto-resumen"><?php echo htmlspecialchars($objeto['nombre'], ENT_QUOTES, 'UTF-8'); ?></summary>
                <div class="objeto-informacion">
                    <?php include __DIR__ . '/ficha.php'; ?>
                    <p class="objeto-enlace-ficha">
                        <a href="index.php?page=objetos/<?php echo rawurlencode($objeto['slug']); ?>">Abrir ficha individual</a>
                    </p>
                </div>
            </details>
        </li>
    <?php } ?>
</ul>
