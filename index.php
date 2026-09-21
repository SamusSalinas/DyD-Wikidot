<?php
$pagina_solicitada = isset($_GET['page']) ? $_GET['page'] : 'inicio';
$pagina_segura = str_replace(array('../', '..\\'), '', $pagina_solicitada);
$ruta_archivo = "paginas/" . $pagina_segura . ".html";
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
                    <li><a href="index.php?page=objetos_magicos">Objetos Mágicos</a></li>
                </ul>
            </nav>

            <!-- ÁREA DE CONTENIDO PRINCIPAL -->
            <main class="columna-contenido">
                <?php
                if (file_exists($ruta_archivo)) {
                    include($ruta_archivo);
                } else {
                    if ($pagina_segura === 'inicio') {
                        echo "<h2>Bienvenido a la Wiki</h2>";
                        echo "<p>Para comenzar, debes crear un archivo llamado <b>inicio.html</b> dentro de la carpeta <b>paginas/</b>.</p>";
                    } else {
                        echo "<h2>Página no encontrada</h2>";
                        echo "<p>La página <b>" . htmlspecialchars($pagina_segura) . "</b> no ha sido generada todavía.</p>";
                    }
                }
                ?>
            </main>

        </div>
    </div>

</body>
</html>