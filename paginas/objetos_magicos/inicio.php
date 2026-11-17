<?php
require_once __DIR__ . '/../../includes/conexion.php';

$rareza_configuracion = array(
    'Common' => array('slug' => 'common', 'boton' => 'Common'),
    'Uncommon' => array('slug' => 'uncommon', 'boton' => 'Uncommon'),
    'Rare' => array('slug' => 'rare', 'boton' => 'Rare'),
    'Very Rare' => array('slug' => 'very-rare', 'boton' => 'Very Rare'),
    'Legendary' => array('slug' => 'legendary', 'boton' => 'Legendary'),
    'Artifact' => array('slug' => 'artifact', 'boton' => 'Artifact'),
    'Unique' => array('slug' => 'unique', 'boton' => 'Unique'),
    '???' => array('slug' => 'desconocido', 'boton' => '???')
);

try {
    $objetos_magicos = conexion_bd()->query(
        "SELECT id, nombre, rareza, tipo, sintonizacion, fuente,
                (descripcion IS NOT NULL AND TRIM(descripcion) <> '') AS tiene_descripcion
         FROM objetos_magicos
         ORDER BY FIELD(rareza, 'Common', 'Uncommon', 'Rare', 'Very Rare', 'Legendary', 'Artifact', 'Unique', '???'), nombre ASC"
    )->fetchAll();
} catch (PDOException $error) {
    error_log('Error al cargar objetos mágicos: ' . $error->getMessage());
    http_response_code(500);
    echo '<h2>Error al cargar los objetos mágicos</h2>';
    echo '<p>No se pudo consultar el catálogo en la base de datos.</p>';
    return;
}

$objetos_por_rareza = array();
foreach ($rareza_configuracion as $rareza => $configuracion) {
    $objetos_por_rareza[$rareza] = array();
}
foreach ($objetos_magicos as $objeto) {
    if (isset($objetos_por_rareza[$objeto['rareza']])) {
        $objetos_por_rareza[$objeto['rareza']][] = $objeto;
    }
}
?>
<h2>Objetos mágicos</h2>
<p>Explora el catálogo por rareza. Solo los objetos con descripción disponible tienen enlace; el resto se muestra sin enlace.</p>

<div class="rareza-filtros" role="group" aria-label="Filtrar por rareza">
    <?php foreach ($rareza_configuracion as $rareza => $configuracion) { ?>
        <button
            class="btn btn-sm rareza-filtro"
            type="button"
            data-rareza="<?php echo htmlspecialchars($configuracion['slug'], ENT_QUOTES, 'UTF-8'); ?>"
            aria-controls="rareza-<?php echo htmlspecialchars($configuracion['slug'], ENT_QUOTES, 'UTF-8'); ?>"
            aria-pressed="<?php echo $configuracion['slug'] === 'common' ? 'true' : 'false'; ?>">
            <?php echo htmlspecialchars($configuracion['boton'], ENT_QUOTES, 'UTF-8'); ?>
        </button>
    <?php } ?>
</div>

<?php foreach ($rareza_configuracion as $rareza => $configuracion) { ?>
    <section
        class="rareza-seccion"
        id="rareza-<?php echo htmlspecialchars($configuracion['slug'], ENT_QUOTES, 'UTF-8'); ?>"
        aria-labelledby="titulo-<?php echo htmlspecialchars($configuracion['slug'], ENT_QUOTES, 'UTF-8'); ?>"
        <?php echo $configuracion['slug'] === 'common' ? '' : 'hidden'; ?>>
        <h3 id="titulo-<?php echo htmlspecialchars($configuracion['slug'], ENT_QUOTES, 'UTF-8'); ?>">
            <?php echo htmlspecialchars($rareza, ENT_QUOTES, 'UTF-8'); ?>
            <span class="badge rounded-pill text-bg-light"><?php echo count($objetos_por_rareza[$rareza]); ?> objetos</span>
        </h3>
        <?php if (count($objetos_por_rareza[$rareza]) === 0) { ?>
            <p role="status">No hay objetos de esta rareza en el catálogo.</p>
        <?php } else { ?>
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle tabla-objetos-magicos">
                    <thead>
                        <tr>
                            <th scope="col">Objeto</th>
                            <th scope="col">Tipo</th>
                            <th scope="col">Sintonización</th>
                            <th scope="col">Fuente</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($objetos_por_rareza[$rareza] as $objeto) { ?>
                            <tr>
                                <th scope="row">
                                    <?php if (!empty($objeto['tiene_descripcion'])) { ?>
                                    <a href="index.php?page=objetos_magicos/detalle&amp;id=<?php echo (int) $objeto['id']; ?>">
                                        <?php echo htmlspecialchars($objeto['nombre'], ENT_QUOTES, 'UTF-8'); ?>
                                    </a>
                                    <?php } else { ?>
                                    <?php echo htmlspecialchars($objeto['nombre'], ENT_QUOTES, 'UTF-8'); ?>
                                    <?php } ?>
                                </th>
                                <td><?php echo htmlspecialchars($objeto['tipo'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo $objeto['sintonizacion'] === '-' ? 'No' : htmlspecialchars($objeto['sintonizacion'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($objeto['fuente'], ENT_QUOTES, 'UTF-8'); ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        <?php } ?>
    </section>
<?php } ?>

<script>
(() => {
    const botones = document.querySelectorAll('.rareza-filtro');
    const secciones = document.querySelectorAll('.rareza-seccion');

    botones.forEach(boton => {
        boton.addEventListener('click', () => {
            const seleccion = boton.dataset.rareza;
            botones.forEach(item => item.setAttribute('aria-pressed', item === boton ? 'true' : 'false'));
            secciones.forEach(seccion => {
                seccion.hidden = seccion.id !== `rareza-${seleccion}`;
            });
        });
    });
})();
</script>
