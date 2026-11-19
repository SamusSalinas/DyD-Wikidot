<?php
require_once __DIR__ . '/../../includes/conexion.php';

// Tomamos el ID de la clase desde la URL (por defecto la clase 1)
$id = isset($_GET['id']) ? (int) $_GET['id'] : 1;

// 1. Consultar la información principal de la clase
$sql = "SELECT * FROM clases WHERE id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows > 0):
    $clase = $resultado->fetch_assoc();
    $stmt->close();

    // 2. Consultar el equipo inicial desde la tabla intermedia unida a objetos_equipo
    $sql_equipo = "SELECT o.nombre, o.slug, ce.cantidad 
                   FROM clase_equipo ce 
                   JOIN objetos_equipo o ON ce.objeto_slug = o.slug 
                   WHERE ce.clase_id = ?";
    $stmt_eq = $conexion->prepare($sql_equipo);
    $stmt_eq->bind_param("i", $id);
    $stmt_eq->execute();
    $resultado_equipo = $stmt_eq->get_result();

    // 3. Consultar subclases asociadas a esta clase
    $sql_sub = "SELECT * FROM subclases WHERE clase_id = ?";
    $stmt_sub = $conexion->prepare($sql_sub);
    $stmt_sub->bind_param("i", $id);
    $stmt_sub->execute();
    $resultado_sub = $stmt_sub->get_result();

    // Procesamos el nombre del dado para la imagen PNG (ej. "d8" o "1d8" -> "d8")
    $dado_texto = strtolower(trim($clase['dado_golpe']));
    preg_match('/d\d+/', $dado_texto, $match);
    $nombre_dado = $match[0] ?? 'd8';

    // Mapeo auxiliar por si el atributo principal viene abreviado o completo
    $atributo_raw = strtolower(trim($clase['caracteristica_principal'] ?? ''));
    $mapa_atributos = [
        'fuerza' => 'str',
        'str' => 'str',
        'strength' => 'str',
        'destreza' => 'dex',
        'dex' => 'dex',
        'dexterity' => 'dex',
        'constitución' => 'con',
        'constitucion' => 'con',
        'con' => 'con',
        'constitution' => 'con',
        'inteligencia' => 'int',
        'int' => 'int',
        'intelligence' => 'int',
        'sabiduría' => 'wis',
        'sabiduria' => 'wis',
        'wis' => 'wis',
        'wisdom' => 'wis',
        'carisma' => 'cha',
        'cha' => 'cha',
        'charisma' => 'cha'
    ];
    $attr_index = $mapa_atributos[$atributo_raw] ?? 'str';

    if (!function_exists('clase_obtener_icono_en')) {
        function clase_obtener_icono_en($nombre_clase)
        {
            static $mapa_clases_en = array(
            'barbaro' => 'barbarian',
            'bardo' => 'bard',
            'brujo' => 'warlock',
            'clerigo' => 'cleric',
            'druida' => 'druid',
            'explorador' => 'ranger',
            'guerrero' => 'fighter',
            'hechicero' => 'sorcerer',
            'mago' => 'wizard',
            'monje' => 'monk',
            'paladin' => 'paladin',
            'picaro' => 'rogue',
            'artificiero' => 'artificer',
            );

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

            $slug = preg_replace('/[^a-z0-9_-]+/', '-', $sin_tildes);
            return trim((string) $slug, '-');
        }
    }

    $icono_clase = clase_obtener_icono_en($clase['nombre'] ?? '');
    ?>

    <div class="container my-4">
        <!-- Encabezado de la Clase -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="d-flex align-items-center mb-0 border-0 text-decoration-none">
                <?php if ($icono_clase !== ''): ?>
                    <img src="images/<?php echo htmlspecialchars($icono_clase, ENT_QUOTES, 'UTF-8'); ?>.png"
                        alt="<?php echo htmlspecialchars($clase['nombre'], ENT_QUOTES, 'UTF-8'); ?>"
                        class ="me-3";
                        style="width: 128px; height: 128px; object-fit: contain; flex-shrink: 0;"
                        onerror="this.style.display='none';">
                <?php endif; ?>
                <span><?php echo htmlspecialchars($clase['nombre'], ENT_QUOTES, 'UTF-8'); ?></span>
            </h2>
            <a href="index.php?page=clases/inicio" class="btn btn-outline-secondary btn-sm">Volver a todas las clases</a>
        </div>
        <hr>

        <!-- Tarjeta de Resumen -->
        <div class="card shadow-sm mb-4" style="border-color: #2e4d3b;">
            <div class="card-header bg-light text-dark fw-bold">Resumen de la clase</div>
            <div class="card-body">

                <!-- Atributo principal -->
                <div class="row mb-3 align-items-center">
                    <div class="col-md-4 fw-bold">Atributo principal:</div>
                    <div class="col-md-8">
                        <span class="badge bg-success d-inline-flex align-items-center gap-1 px-2 py-1"
                            style="font-size: 0.9rem;">
                            <img src="images/attributes/<?php echo $attr_index; ?>.png" alt=""
                                style="width: 16px; height: 16px;" onerror="this.style.display='none';">
                            <?php echo htmlspecialchars($clase['caracteristica_principal']); ?>
                        </span>
                    </div>
                </div>

                <!-- Dado de golpe con imagen PNG -->
                <div class="row mb-3 align-items-center">
                    <div class="col-md-4 fw-bold">Dado de golpe:</div>
                    <div class="col-md-8">
                        <div class="d-flex align-items-center gap-2">
                            <img src="images/<?php echo $nombre_dado; ?>.png"
                                alt="Dado <?php echo htmlspecialchars($clase['dado_golpe']); ?>"
                                style="width: 28px; height: 28px; object-fit: contain;"
                                onerror="this.style.display='none';">
                            <span class="fw-bold text-dark"><?php echo htmlspecialchars($clase['dado_golpe']); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Tiradas de salvación -->
                <div class="row mb-2">
                    <div class="col-md-4 fw-bold">Tiradas de salvación:</div>
                    <div class="col-md-8"><?php echo htmlspecialchars($clase['salvaciones']); ?></div>
                </div>

            </div>
        </div>


        <!-- Equipo Inicial Mejorado con Tabla RPG -->
        <h3 class="h4 mt-4 mb-3">Equipo inicial</h3>
        <div class="card shadow-sm mb-4 border-success">
            <div class="card-header bg-success text-white fw-bold d-flex justify-content-between align-items-center">
                <span>Inventario y Dotación Inicial</span>
                <span class="badge bg-light text-dark">D&D 5e</span>
            </div>
            <div class="card-body p-0">
                <?php
                // Aseguramos en la consulta que traiga los campos extra de la tabla objetos_equipo
                $sql_equipo = "SELECT o.nombre, o.slug, o.categoria, o.peso, o.coste, ce.cantidad 
                           FROM clase_equipo ce 
                           JOIN objetos_equipo o ON ce.objeto_slug = o.slug 
                           WHERE ce.clase_id = ?";
                $stmt_eq = $conexion->prepare($sql_equipo);
                $stmt_eq->bind_param("i", $id);
                $stmt_eq->execute();
                $resultado_equipo = $stmt_eq->get_result();

                if ($resultado_equipo->num_rows > 0):
                    ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Objeto / Equipo</th>
                                    <th>Categoría</th>
                                    <th class="text-center">Cantidad</th>
                                    <th class="text-center">Peso</th>
                                    <th class="text-end pe-3">Costo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($item = $resultado_equipo->fetch_assoc()): ?>
                                    <tr>
                                        <td class="ps-3 fw-bold text-dark">
                                            <?php echo htmlspecialchars($item['nombre']); ?>
                                            <small class="d-block text-muted fw-normal"
                                                style="font-size: 0.75rem;"><?php echo htmlspecialchars($item['slug']); ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary text-uppercase" style="font-size: 0.7rem;">
                                                <?php echo htmlspecialchars($item['categoria'] ?? 'Equipo'); ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-dark">x<?php echo (int) $item['cantidad']; ?></span>
                                        </td>
                                        <td class="text-center text-muted small">
                                            <?php echo !empty($item['peso']) ? htmlspecialchars($item['peso']) . ' lb' : '-'; ?>
                                        </td>
                                        <td class="text-end pe-3 text-muted small">
                                            <?php echo !empty($item['costo']) ? htmlspecialchars($item['costo']) : '-'; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="p-3 text-muted text-center mb-0">
                        No hay equipo inicial registrado para esta clase.
                    </div>
                <?php endif;
                if (isset($stmt_eq)) {
                    $stmt_eq->close();
                }
                ?>
            </div>
        </div>

        <!-- Subclases Disponibles -->
        <h3 class="h4 mt-4 mb-3">Subclases disponibles</h3>
        <?php if ($resultado_sub->num_rows > 0): ?>
            <div class="row g-4 mb-4">
                <?php while ($sub = $resultado_sub->fetch_assoc()): ?>
                    <div class="col-md-6">
                        <div class="card h-100 border-success shadow-sm">
                            <div class="card-header bg-success text-white fw-bold">
                                <?php echo htmlspecialchars($sub['nombre']); ?>
                            </div>
                            <div class="card-body">
                                <p class="card-text small text-muted mb-0" style="white-space: pre-line;">
                                    <?php echo htmlspecialchars($sub['descripcion']); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-light border text-muted mb-4">
                No hay subclases registradas para esta clase todavía.
            </div>
        <?php endif; ?>

        <div class="mt-4">
            <a href="index.php?page=clases/inicio" class="btn btn-secondary">Volver a todas las clases</a>
        </div>
    </div>

    <?php

    ?>

<?php endif; ?>