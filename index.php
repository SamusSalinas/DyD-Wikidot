<?php
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
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>D&D 5e Wiki</title>
    <!-- Aquí vinculamos tu nuevo archivo estructural -->
    <link rel="stylesheet" href="estructura.css">
</head>
<body>

    <div class="contenedor-maestro">
        
        <!-- Encabezado principal -->
        <header class="cabecera">
            <h1>Wiki de Dungeons & Dragons 5e</h1>
        </header>

        <!-- Cuerpo de dos columnas -->
        <div class="cuerpo-pagina">
            
            <!-- BARRA LATERAL (Menú de navegación) -->
            <nav class="columna-navegacion">
                <h3>Navegación</h3>
                <ul>
                    <li><a href="index.php?page=inicio">Página Principal</a></li>
                    <li><a href="index.php?page=quiz/inicio">¿Que clase de DnD soy?</a></li>
                    <li><a href="index.php?page=personajes/hoja">Hoja de personaje</a></li>
                </ul>
                
                <h3>Reglas de Creación</h3>
                <ul>
                    <li><a href="index.php?page=razas/inicio">Razas</a></li>
                    <li><a href="index.php?page=clases/inicio">Clases</a></li> 
                    <li><a href="index.php?page=trasfondos/inicio">Trasfondos</a></li>
                    <li><a href="index.php?page=dotes/inicio">Dotes</a></li>
                </ul>

                <h3>Magia y Equipo</h3>
                <ul>
                    <li><a href="index.php?page=hechizos/inicio">Listas de Hechizos</a></li>
                    <li><a href="index.php?page=objetos/inicio">Equipamiento (Armas, etc.)</a></li>
                    <li><a href="index.php?page=objetos_magicos/inicio">Objetos Mágicos</a></li>
                </ul>
            </nav>

            <!-- ÁREA DE CONTENIDO PRINCIPAL -->
            <main class="columna-contenido">
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
            </main>

        </div>
    </div>

</body>
</html>