<?php
require_once __DIR__ . '/../../includes/conexion.php';

try {
    $consulta = conexion_bd()->prepare(
        "SELECT nombre, nivel, escuela, descripcion
         FROM hechizos
         WHERE FIND_IN_SET(:clase, REPLACE(clases_permitidas, ' ', '')) > 0
         ORDER BY nivel ASC, nombre ASC"
    );
    $consulta->execute(array('clase' => $clase_hechizos['filtro']));
    $hechizos = $consulta->fetchAll();
} catch (PDOException $error) {
    error_log('Error al cargar hechizos: ' . $error->getMessage());
    http_response_code(500);
    echo '<h2>Error al cargar los hechizos</h2>';
    echo '<p>No se pudo consultar la lista de hechizos en la base de datos.</p>';
    return;
}

$hechizos_por_nivel = array();
foreach ($hechizos as $hechizo) {
    $nivel = (int) $hechizo['nivel'];
    if (!isset($hechizos_por_nivel[$nivel])) {
        $hechizos_por_nivel[$nivel] = array();
    }
    $hechizos_por_nivel[$nivel][] = $hechizo;
}
?>
<h2>Hechizos de <?php echo htmlspecialchars($clase_hechizos['nombre'], ENT_QUOTES, 'UTF-8'); ?></h2>
<p>Organizados por nivel y en orden alfabético.</p>
<p><a class="btn btn-sm btn-outline-secondary" href="index.php?page=hechizos/inicio">Volver a las listas de clases</a></p>

<?php if (count($hechizos_por_nivel) === 0) { ?>
    <p role="status">No hay hechizos de esta clase en el catálogo.</p>
<?php } ?>

<?php foreach ($hechizos_por_nivel as $nivel => $lista) { ?>
    <section class="hechizos-nivel">
        <h3>
            <?php echo $nivel === 0 ? 'Trucos (nivel 0)' : 'Nivel ' . (int) $nivel; ?>
            <span class="badge rounded-pill text-bg-light"><?php echo count($lista); ?> hechizos</span>
        </h3>
        <ul class="lista-hechizos list-unstyled row row-cols-1 row-cols-md-2 row-cols-xl-3 g-2">
            <?php foreach ($lista as $hechizo) { ?>
                <li class="col">
                    <details class="hechizo-detalle">
                        <summary class="hechizo-item">
                            <span><?php echo htmlspecialchars($hechizo['nombre'], ENT_QUOTES, 'UTF-8'); ?></span>
                            <span class="hechizo-indicacion">Ver detalles</span>
                        </summary>
                        <div class="hechizo-informacion">
                            <dl class="hechizo-metadatos">
                                <div>
                                    <dt>Nivel</dt>
                                    <dd><?php echo (int) $hechizo['nivel'] === 0 ? 'Truco (nivel 0)' : (int) $hechizo['nivel']; ?></dd>
                                </div>
                                <?php if (!empty($hechizo['escuela'])) { ?>
                                    <div>
                                        <dt>Escuela</dt>
                                        <dd><?php echo htmlspecialchars($hechizo['escuela'], ENT_QUOTES, 'UTF-8'); ?></dd>
                                    </div>
                                <?php } ?>
                            </dl>
                            <p><?php
                                $descripcion = isset($hechizo['descripcion']) && trim($hechizo['descripcion']) !== ''
                                    ? $hechizo['descripcion']
                                    : 'Descripción no disponible.';
                                echo nl2br(htmlspecialchars($descripcion, ENT_QUOTES, 'UTF-8'));
                            ?></p>
                        </div>
                    </details>
                </li>
            <?php } ?>
        </ul>
    </section>
<?php } ?>
