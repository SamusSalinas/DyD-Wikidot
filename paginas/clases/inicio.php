<?php
require_once __DIR__ . '/../../includes/conexion.php';

$sql = "SELECT id, nombre, dado_golpe, caracteristica_principal FROM clases ORDER BY id ASC";
$resultado = $conexion->query($sql);

// Mapeo de nombres en español (y variantes) al slug del archivo en inglés en minúscula
$mapa_clases_en = array(
    'barbaro'      => 'barbarian',
    'bardo'        => 'bard',
    'brujo'        => 'warlock',
    'clerigo'      => 'cleric',
    'druida'       => 'druid',
    'explorador (ranger)'   => 'ranger',
    'guerrero'     => 'fighter',
    'hechicero'    => 'sorcerer',
    'mago'         => 'wizard',
    'monje'        => 'monk',
    'paladin'      => 'paladin',
    'picaro'       => 'rogue',
    'artificiero'  => 'artificer',
);

function clase_obtener_icono_en($nombre_clase, array $mapa_clases_en)
{
    $limpio = mb_strtolower(trim((string) $nombre_clase), 'UTF-8');
    $sin_tildes = strtr($limpio, array(
        'á' => 'a',
        'é' => 'e',
        'í' => 'i',
        'ó' => 'o',
        'ú' => 'u',
        'ü' => 'u',
        'ñ' => 'n',
    ));

    if (isset($mapa_clases_en[$sin_tildes])) {
        return $mapa_clases_en[$sin_tildes];
    }

    // Si ya viene en inglés o como otro nombre, limpiamos a minúsculas alfanumérico/guion
    $slug = preg_replace('/[^a-z0-9_-]+/', '-', $sin_tildes);
    return trim((string) $slug, '-');
}
?>

<div class="container my-4">
    <!-- Encabezado de la sección -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="fw-bold text-dark">Clases disponibles</h2>
            <p class="text-muted">Seleccioná una clase para consultar sus estadísticas y equipo inicial.</p>
        </div>
    </div>
    <hr class="mb-4">

    <!-- Grilla de Clases -->
    <div class="row g-4">
        <?php while ($fila = $resultado->fetch_assoc()):
            // Limpiamos el texto del dado (ej. "d8" o "1d8") para obtener el nombre de la imagen PNG
            $dado_texto = strtolower(trim($fila['dado_golpe']));
            preg_match('/d\d+/', $dado_texto, $match);
            $nombre_dado = $match[0] ?? 'd8';

            // Nombre del ícono de clase en inglés y minúscula (ej. warlock.png, fighter.png)
            $icono_clase = clase_obtener_icono_en($fila['nombre'], $mapa_clases_en);
        ?>
            <div class="col-md-4 col-sm-6">
                <div class="card h-100 shadow-sm border-success transition-card">
                    <div class="card-body d-flex flex-column justify-content-between">
                        <div>
                            <!-- Ícono a la izquierda + Nombre de la clase como enlace -->
                            <h4 class="card-title d-flex align-items-center gap-2 mb-0">
                                <img src="images/<?php echo htmlspecialchars($icono_clase, ENT_QUOTES, 'UTF-8'); ?>.png"
                                     alt="<?php echo htmlspecialchars($fila['nombre'], ENT_QUOTES, 'UTF-8'); ?>"
                                     style="width: 64px; height: 64px; object-fit: contain; flex-shrink: 0;"
                                     onerror="this.style.display='none';">
                                <a href="index.php?page=clases/ver_clase&id=<?php echo (int) $fila['id']; ?>"
                                   class="text-success text-decoration-none stretched-link">
                                    <?php echo htmlspecialchars($fila['nombre'], ENT_QUOTES, 'UTF-8'); ?>
                                </a>
                            </h4>
                            <hr class="my-2">
                            <!-- Característica principal -->
                            <p class="card-text text-secondary small mb-3">
                                <strong>Atributo principal:</strong> <?php echo htmlspecialchars($fila['caracteristica_principal'], ENT_QUOTES, 'UTF-8'); ?>
                            </p>
                        </div>

                        <!-- Dado de golpe con imagen PNG -->
                        <div class="d-flex align-items-center gap-2 bg-light p-2 rounded border">
                            <img src="images/<?php echo htmlspecialchars($nombre_dado, ENT_QUOTES, 'UTF-8'); ?>.png"
                                 alt="Dado <?php echo htmlspecialchars($fila['dado_golpe'], ENT_QUOTES, 'UTF-8'); ?>"
                                 style="width: 32px; height: 32px; object-fit: contain;"
                                 onerror="this.style.display='none';">
                            <div class="small">
                                <span class="text-muted d-block" style="font-size: 0.75rem; line-height: 1;">Dado de Golpe</span>
                                <strong class="text-dark"><?php echo htmlspecialchars($fila['dado_golpe'], ENT_QUOTES, 'UTF-8'); ?></strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<style>
    .transition-card {
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }
    .transition-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
    }
</style>