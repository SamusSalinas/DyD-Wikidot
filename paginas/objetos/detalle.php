<?php
require_once __DIR__ . '/../../includes/conexion.php';

try {
    $consulta = conexion_bd()->prepare(
        'SELECT slug, nombre, categoria, coste, peso, dano, clase_armadura, propiedades, descripcion
         FROM objetos_equipo
         WHERE slug = :slug'
    );
    $consulta->execute(array('slug' => $objeto_slug));
    $objeto = $consulta->fetch();
} catch (PDOException $error) {
    error_log('Error al cargar ficha de objeto: ' . $error->getMessage());
    http_response_code(500);
    echo '<h2>Error al cargar el objeto</h2>';
    echo '<p>No se pudo consultar la ficha en la base de datos.</p>';
    return;
}

if (!$objeto) {
    http_response_code(404);
    echo '<h2>Objeto no encontrado</h2>';
    echo '<p>La ficha solicitada no existe en el catálogo.</p>';
    return;
}
?>
<h2><?php echo htmlspecialchars($objeto['nombre'], ENT_QUOTES, 'UTF-8'); ?></h2>
<p><a class="btn btn-sm btn-outline-secondary" href="index.php?page=objetos/inicio">Volver al equipamiento</a></p>
<?php include __DIR__ . '/ficha.php'; ?>
