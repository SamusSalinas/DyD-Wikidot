<?php
require_once __DIR__ . '/../../includes/conexion.php';

$slug = isset($_GET['clase']) ? trim($_GET['clase']) : 'wizard';

// Mapeo del slug de la URL al nombre que guardaste en la columna 'clases_permitidas' de tu BD
$mapa_clases = [
    'bard' => 'Bardo',
    'cleric' => 'Clérigo',
    'druid' => 'Druida',
    'paladin' => 'Paladín',
    'ranger' => 'Explorador',
    'sorcerer' => 'Hechicero',
    'warlock' => 'Brujo',
    'wizard' => 'Mago'
];

$nombre_clase_espanol = isset($mapa_clases[$slug]) ? $mapa_clases[$slug] : 'Mago';
$nombre_clase_ingles = ucfirst($slug);

// Configuración de niveles para las pestañas
$nivel_configuracion = array(
    'Cantrips' => array('slug' => 'truco', 'boton' => 'Cantrips', 'nivel_bd' => 0),
    'Nivel 1' => array('slug' => 'nivel-1', 'boton' => 'Nivel 1', 'nivel_bd' => 1),
    'Nivel 2' => array('slug' => 'nivel-2', 'boton' => 'Nivel 2', 'nivel_bd' => 2),
    'Nivel 3' => array('slug' => 'nivel-3', 'boton' => 'Nivel 3', 'nivel_bd' => 3),
    'Nivel 4' => array('slug' => 'nivel-4', 'boton' => 'Nivel 4', 'nivel_bd' => 4),
    'Nivel 5' => array('slug' => 'nivel-5', 'boton' => 'Nivel 5', 'nivel_bd' => 5),
    'Nivel 6' => array('slug' => 'nivel-6', 'boton' => 'Nivel 6', 'nivel_bd' => 6),
    'Nivel 7' => array('slug' => 'nivel-7', 'boton' => 'Nivel 7', 'nivel_bd' => 7),
    'Nivel 8' => array('slug' => 'nivel-8', 'boton' => 'Nivel 8', 'nivel_bd' => 8),
    'Nivel 9' => array('slug' => 'nivel-9', 'boton' => 'Nivel 9', 'nivel_bd' => 9)
);

try {
    // Consultamos los hechizos de esta clase ordenados por nivel y nombre
    $sql = "SELECT id, nombre, nivel, escuela, clases_permitidas,
                   (descripcion IS NOT NULL AND TRIM(descripcion) <> '') AS tiene_descripcion
            FROM hechizos 
            WHERE clases_permitidas LIKE ? 
            ORDER BY nivel ASC, nombre ASC";
    
    $stmt = $conexion->prepare($sql);
    $param_like = "%" . $nombre_clase_espanol . "%";
    $stmt->bind_param("s", $param_like);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    $hechizos = array();
    while ($fila = $resultado->fetch_assoc()) {
        $hechizos[] = $fila;
    }
    $stmt->close();
} catch (Exception $error) {
    error_log('Error al cargar hechizos de la clase: ' . $error->getMessage());
    $hechizos = array();
}

// Agrupar los hechizos obtenidos por nivel
$hechizos_por_nivel = array();
foreach ($nivel_configuracion as $nombre_nivel => $configuracion) {
    $hechizos_por_nivel[$nombre_nivel] = array();
}

foreach ($hechizos as $hechizo) {
    foreach ($nivel_configuracion as $nombre_nivel => $configuracion) {
        if ((int)$hechizo['nivel'] === $configuracion['nivel_bd']) {
            $hechizos_por_nivel[$nombre_nivel][] = $hechizo;
            break;
        }
    }
}
?>

<h2>Hechizos de <?php echo htmlspecialchars($nombre_clase_ingles); ?></h2>
<p><a href="index.php?page=hechizos/inicio">← Volver a Todas las Clases Mágicas</a></p>
<p>Explora los conjuros de esta clase organizados por nivel. Selecciona un nivel arriba para filtrar.</p>
<hr>

<!-- Botones de Filtro Superior por Nivel -->
<div class="rareza-filtros" role="group" aria-label="Filtrar por nivel de hechizo">
    <?php 
    $primer_boton = true;
    foreach ($nivel_configuracion as $nombre_nivel => $configuracion) { 
    ?>
        <button
            class="btn btn-sm rareza-filtro"
            type="button"
            data-rareza="<?php echo htmlspecialchars($configuracion['slug'], ENT_QUOTES, 'UTF-8'); ?>"
            aria-controls="nivel-<?php echo htmlspecialchars($configuracion['slug'], ENT_QUOTES, 'UTF-8'); ?>"
            aria-pressed="<?php echo $primer_boton ? 'true' : 'false'; ?>">
            <?php echo htmlspecialchars($configuracion['boton'], ENT_QUOTES, 'UTF-8'); ?>
        </button>
    <?php 
        $primer_boton = false;
    } 
    ?>
</div>

<!-- Secciones de Contenido con Tablas por Nivel -->
<?php 
$primera_seccion = true;
foreach ($nivel_configuracion as $nombre_nivel => $configuracion) { 
?>
    <section
        class="rareza-seccion"
        id="nivel-<?php echo htmlspecialchars($configuracion['slug'], ENT_QUOTES, 'UTF-8'); ?>"
        aria-labelledby="titulo-<?php echo htmlspecialchars($configuracion['slug'], ENT_QUOTES, 'UTF-8'); ?>"
        <?php echo $primera_seccion ? '' : 'hidden'; ?>>
        
        <h3 id="titulo-<?php echo htmlspecialchars($configuracion['slug'], ENT_QUOTES, 'UTF-8'); ?>">
            <?php echo htmlspecialchars($nombre_nivel, ENT_QUOTES, 'UTF-8'); ?>
            <span class="badge rounded-pill text-bg-light"><?php echo count($hechizos_por_nivel[$nombre_nivel]); ?> conjuros</span>
        </h3>

        <?php if (count($hechizos_por_nivel[$nombre_nivel]) === 0) { ?>
            <p role="status">No hay hechizos de este nivel para esta clase en el catálogo.</p>
        <?php } else { ?>
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle tabla-objetos-magicos">
                    <thead>
                        <tr>
                            <th scope="col">Conjuro</th>
                            <th scope="col">Escuela</th>
                            <th scope="col">Clases Permitidas</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($hechizos_por_nivel[$nombre_nivel] as $hechizo) { ?>
                            <tr>
                                <th scope="row">
                                    <?php if (!empty($hechizo['tiene_descripcion'])) { ?>
                                        <a href="index.php?page=hechizos/ver_hechizo&amp;id=<?php echo (int) $hechizo['id']; ?>">
                                            <?php echo htmlspecialchars($hechizo['nombre'], ENT_QUOTES, 'UTF-8'); ?>
                                        </a>
                                    <?php } else { ?>
                                        <?php echo htmlspecialchars($hechizo['nombre'], ENT_QUOTES, 'UTF-8'); ?>
                                    <?php } ?>
                                </th>
                                <td><?php echo htmlspecialchars($hechizo['escuela'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($hechizo['clases_permitidas'], ENT_QUOTES, 'UTF-8'); ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        <?php } ?>
    </section>
<?php 
    $primera_seccion = false;
} 
?>

<!-- Script JavaScript para alternar entre las pestañas de nivel -->
<script>
(() => {
    const botones = document.querySelectorAll('.rareza-filtro');
    const secciones = document.querySelectorAll('.rareza-seccion');

    botones.forEach(boton => {
        boton.addEventListener('click', () => {
            const seleccion = boton.dataset.rareza;
            botones.forEach(item => item.setAttribute('aria-pressed', item === boton ? 'true' : 'false'));
            secciones.forEach(seccion => {
                seccion.hidden = seccion.id !== `nivel-${seleccion}`;
            });
        });
    });
})();
</script>