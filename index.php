<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$pagina_solicitada = isset($_GET['page']) && is_string($_GET['page'])
    ? $_GET['page']
    : (isset($_GET['page']) ? '' : 'inicio');
$pagina_segura = preg_match('/\A[a-zA-Z0-9_-]+(?:\/[a-zA-Z0-9_-]+)*\z/', $pagina_solicitada)
    ? $pagina_solicitada
    : '';
$ruta_base = __DIR__ . DIRECTORY_SEPARATOR . 'paginas' . DIRECTORY_SEPARATOR
    . str_replace('/', DIRECTORY_SEPARATOR, $pagina_segura);
$ruta_html = $ruta_base . '.html';
$ruta_php = $ruta_base . '.php';
$ruta_archivo = is_file($ruta_html) ? $ruta_html : (is_file($ruta_php) ? $ruta_php : null);
$es_quiz = $pagina_solicitada === 'quiz/inicio';

$clases_hechizos = array(
    'barbarian' => array('nombre' => 'Bárbaro', 'filtro' => 'Bárbaro'),
    'bard' => array('nombre' => 'Bardo', 'filtro' => 'Bardo'),
    'cleric' => array('nombre' => 'Clérigo', 'filtro' => 'Clérigo'),
    'druid' => array('nombre' => 'Druida', 'filtro' => 'Druida'),
    'fighter' => array('nombre' => 'Guerrero', 'filtro' => 'Guerrero'),
    'monk' => array('nombre' => 'Monje', 'filtro' => 'Monje'),
    'paladin' => array('nombre' => 'Paladín', 'filtro' => 'Paladín'),
    'ranger' => array('nombre' => 'Explorador (Ranger)', 'filtro' => 'Explorador'),
    'rogue' => array('nombre' => 'Pícaro', 'filtro' => 'Pícaro'),
    'sorcerer' => array('nombre' => 'Hechicero', 'filtro' => 'Hechicero'),
    'warlock' => array('nombre' => 'Brujo', 'filtro' => 'Brujo'),
    'wizard' => array('nombre' => 'Mago', 'filtro' => 'Mago')
);
$rutas_hechizos = array();
foreach ($clases_hechizos as $slug => $clase) {
    $rutas_hechizos['clases/' . $slug . '/hechizos'] = $clase;
}
foreach (array('bard', 'cleric', 'druid', 'paladin', 'ranger', 'sorcerer', 'warlock', 'wizard') as $slug) {
    $rutas_hechizos['hechizos/' . $slug] = $clases_hechizos[$slug];
}

$clase_hechizos = null;
if (isset($rutas_hechizos[$pagina_segura])) {
    $ruta_archivo = __DIR__ . DIRECTORY_SEPARATOR . 'paginas' . DIRECTORY_SEPARATOR
        . 'hechizos' . DIRECTORY_SEPARATOR . 'lista.php';
    $clase_hechizos = $rutas_hechizos[$pagina_segura];
}

$objeto_slug = null;
if ($pagina_segura === 'objetos/inicio') {
    $ruta_archivo = __DIR__ . DIRECTORY_SEPARATOR . 'paginas' . DIRECTORY_SEPARATOR
        . 'objetos' . DIRECTORY_SEPARATOR . 'inicio.php';
} elseif (preg_match('/\Aobjetos\/([a-z0-9-]+)\z/', $pagina_segura, $coincidencias)) {
    $ruta_archivo = __DIR__ . DIRECTORY_SEPARATOR . 'paginas' . DIRECTORY_SEPARATOR
        . 'objetos' . DIRECTORY_SEPARATOR . 'detalle.php';
    $objeto_slug = $coincidencias[1];
}

if ($pagina_segura === 'objetos_magicos/inicio') {
    $ruta_archivo = __DIR__ . DIRECTORY_SEPARATOR . 'paginas' . DIRECTORY_SEPARATOR
        . 'objetos_magicos' . DIRECTORY_SEPARATOR . 'inicio.php';
} elseif ($pagina_segura === 'objetos_magicos/detalle') {
    $ruta_archivo = __DIR__ . DIRECTORY_SEPARATOR . 'paginas' . DIRECTORY_SEPARATOR
        . 'objetos_magicos' . DIRECTORY_SEPARATOR . 'detalle.php';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#19352b">
    <title>D&D 5e Wiki</title>
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="estructura.css?v=<?php echo filemtime(__DIR__ . DIRECTORY_SEPARATOR . 'estructura.css'); ?>">
</head>
<body class="wiki-fondo">

    <div class="contenedor-maestro container-fluid">
        <header class="cabecera">
            <div class="cabecera-contenido">
                <span class="marca-emblema" aria-hidden="true">20</span>
                <div>
                    <p class="marca-kicker">El compendio del aventurero</p>
                    <h1>Wiki de Dungeons &amp; Dragons 5e</h1>
                    <p class="marca-descripcion">Reglas, personajes y secretos para tu próxima aventura.</p>
                </div>
                <span class="marca-edicion d-none d-md-inline-flex">QUINTA<br>EDICIÓN</span>
            </div>
        </header>

        <div class="cuerpo-pagina row g-4<?php echo $es_quiz ? ' quiz-layout' : ''; ?>">
            <?php if (!$es_quiz) { ?>
            <nav class="columna-navegacion col-lg-3" aria-label="Navegación principal">
                <div class="navegacion-panel card border-0">
                    <div class="navegacion-cabecera">
                        <span class="nav-kicker">Tu aventura comienza aquí</span>
                        <h2>Explorar</h2>
                    </div>

                    <section class="grupo-navegacion">
                        <h3>Navegación</h3>
                        <ul class="nav flex-column">
                            <li class="nav-item"><a class="nav-link" href="index.php?page=inicio">Página Principal</a></li>
                            <li class="nav-item"><a class="nav-link" href="index.php?page=quiz/inicio">¿Qué clase de D&amp;D soy?</a></li>
                            <li class="nav-item"><a class="nav-link" href="index.php?page=personajes/hoja">Crear personaje</a></li>
                            <li class="nav-item"><a class="nav-link" href="index.php?page=personajes/lista">Lista de personajes</a></li>
                        </ul>
                    </section>

                    <section class="grupo-navegacion">
                        <h3>Reglas de creación</h3>
                        <ul class="nav flex-column">
                            <li class="nav-item"><a class="nav-link" href="index.php?page=razas/inicio">Razas</a></li>
                            <li class="nav-item"><a class="nav-link" href="index.php?page=clases/inicio">Clases</a></li>
                            <li class="nav-item"><a class="nav-link" href="index.php?page=trasfondos/inicio">Trasfondos</a></li>
                            <li class="nav-item"><a class="nav-link" href="index.php?page=dotes/inicio">Dotes</a></li>
                        </ul>
                    </section>

                    <section class="grupo-navegacion">
                        <h3>Magia y equipo</h3>
                        <ul class="nav flex-column">
                            <li class="nav-item"><a class="nav-link" href="index.php?page=hechizos/inicio">Listas de hechizos</a></li>
                            <li class="nav-item"><a class="nav-link" href="index.php?page=objetos/inicio">Equipamiento</a></li>
                            <li class="nav-item"><a class="nav-link" href="index.php?page=objetos_magicos/inicio">Objetos mágicos</a></li>
                        </ul>
                    </section>

                    <p class="nav-pie">Sabiduría, acero y magia.</p>
                </div>
            </nav>
            <?php } ?>

            <main class="columna-contenido <?php echo $es_quiz ? 'col-12' : 'col-lg-9'; ?>">
                <div class="contenido-panel card border-0">
                    <div class="card-body">
                        <?php
                        if ($ruta_archivo !== null) {
                            include $ruta_archivo;
                        } else {
                            if ($pagina_solicitada === 'inicio') {
                                echo "<h2>Bienvenido a la Wiki</h2>";
                                echo "<p>Para comenzar, debes crear un archivo llamado <b>inicio.html</b> dentro de la carpeta <b>paginas/</b>.</p>";
                            } else {
                                echo "<h2>Página no encontrada</h2>";
                                echo "<p>La página <b>" . htmlspecialchars($pagina_solicitada, ENT_QUOTES, 'UTF-8') . "</b> no ha sido generada todavía.</p>";
                            }
                        }
                        ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

</body>
</html>