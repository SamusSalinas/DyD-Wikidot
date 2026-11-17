<?php
require_once __DIR__ . '/../../includes/conexion.php';

$id_objeto = isset($_GET['id']) && is_string($_GET['id'])
    ? filter_var($_GET['id'], FILTER_VALIDATE_INT)
    : false;

if ($id_objeto === false || $id_objeto === null || $id_objeto < 1) {
    http_response_code(400);
    echo '<h2>Objeto mágico no válido</h2>';
    echo '<p>El enlace no contiene un identificador de objeto válido.</p>';
    return;
}

try {
    $consulta = conexion_bd()->prepare(
        'SELECT nombre, rareza, tipo, sintonizacion, fuente, descripcion, descripcion_fuente
         FROM objetos_magicos
         WHERE id = :id'
    );
    $consulta->execute(array('id' => $id_objeto));
    $objeto = $consulta->fetch();
} catch (PDOException $error) {
    error_log('Error al cargar ficha de objeto mágico: ' . $error->getMessage());
    http_response_code(500);
    echo '<h2>Error al cargar el objeto mágico</h2>';
    echo '<p>No se pudo consultar la ficha en la base de datos.</p>';
    return;
}

if (!$objeto) {
    http_response_code(404);
    echo '<h2>Objeto mágico no encontrado</h2>';
    echo '<p>La ficha solicitada no existe en el catálogo.</p>';
    return;
}
?>
<h2><?php echo htmlspecialchars($objeto['nombre'], ENT_QUOTES, 'UTF-8'); ?></h2>
<p><a class="btn btn-sm btn-outline-secondary" href="index.php?page=objetos_magicos/inicio">Volver al catálogo</a></p>
<dl class="objeto-ficha-metadatos">
    <div>
        <dt>Rareza</dt>
        <dd><?php echo htmlspecialchars($objeto['rareza'], ENT_QUOTES, 'UTF-8'); ?></dd>
    </div>
    <div>
        <dt>Tipo</dt>
        <dd><?php echo htmlspecialchars($objeto['tipo'], ENT_QUOTES, 'UTF-8'); ?></dd>
    </div>
    <div>
        <dt>Sintonización</dt>
        <dd><?php echo $objeto['sintonizacion'] === '-' ? 'No requiere' : htmlspecialchars($objeto['sintonizacion'], ENT_QUOTES, 'UTF-8'); ?></dd>
    </div>
    <div>
        <dt>Fuente</dt>
        <dd><?php echo htmlspecialchars($objeto['fuente'], ENT_QUOTES, 'UTF-8'); ?></dd>
    </div>
</dl>
<?php if (!empty($objeto['descripcion'])) { ?>
    <h3>Descripción</h3>
    <div class="objeto-descripcion">
        <?php
        $bloques_descripcion = preg_split('/\r\n|\r|\n{2,}/', trim($objeto['descripcion']));
        foreach ($bloques_descripcion as $bloque) {
            $lineas = preg_split('/\r\n|\r|\n/', $bloque);
            $es_tabla = count($lineas) > 2
                && strpos($lineas[0], '|') !== false
                && strpos($lineas[1], '-') !== false
                && preg_match('/^\s*\|?[\s:|\-]+\|?\s*$/', $lineas[1]);

            if (!$es_tabla) {
                echo '<p>' . nl2br(htmlspecialchars($bloque, ENT_QUOTES, 'UTF-8')) . '</p>';
                continue;
            }
            ?>
            <div class="table-responsive objeto-descripcion-tabla">
                <table class="table table-sm table-striped align-middle">
                    <thead>
                        <tr>
                            <?php foreach (explode('|', trim($lineas[0], '|')) as $celda) { ?>
                                <th scope="col"><?php echo htmlspecialchars(trim($celda), ENT_QUOTES, 'UTF-8'); ?></th>
                            <?php } ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php for ($indice = 2; $indice < count($lineas); $indice++) { ?>
                            <?php if (strpos($lineas[$indice], '|') === false) { continue; } ?>
                            <tr>
                                <?php foreach (explode('|', trim($lineas[$indice], '|')) as $celda) { ?>
                                    <td><?php echo htmlspecialchars(trim($celda), ENT_QUOTES, 'UTF-8'); ?></td>
                                <?php } ?>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
            <?php
        }
        ?>
    </div>
    <p class="text-muted small">Descripción en inglés. Fuente: <?php echo htmlspecialchars($objeto['descripcion_fuente'], ENT_QUOTES, 'UTF-8'); ?>.</p>
    <?php if ($objeto['descripcion_fuente'] === 'SRD 5.1 (CC-BY-4.0)') { ?>
        <p class="aviso-catalogo">This work includes material taken from the System Reference Document 5.1 ("SRD 5.1") by Wizards of the Coast LLC and available at <a href="https://dnd.wizards.com/resources/systems-reference-document">https://dnd.wizards.com/resources/systems-reference-document</a>. The SRD 5.1 is licensed under the Creative Commons Attribution 4.0 International License available at <a href="https://creativecommons.org/licenses/by/4.0/legalcode">https://creativecommons.org/licenses/by/4.0/legalcode</a>.</p>
    <?php } ?>
<?php } else { ?>
    <p class="aviso-catalogo">No hay una descripción con licencia SRD 5.1 para este objeto en la base de datos.</p>
<?php } ?>
