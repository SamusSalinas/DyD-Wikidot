<?php
// Control de sesión y conexión
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}
require_once __DIR__ . '/../../includes/conexion.php';
require_once __DIR__ . '/../../includes/funciones_personaje.php';

// Helper local para decodificar arrays guardados como JSON o CSV
function pj_ver_decodificar_lista($valor)
{
    if (is_array($valor)) {
        return $valor;
    }
    $texto = trim((string) $valor);
    if ($texto === '') {
        return array();
    }
    if ($texto[0] === '[') {
        $decoded = json_decode($texto, true);
        if (is_array($decoded)) {
            return $decoded;
        }
    }
    return array_values(array_filter(array_map('trim', explode(',', $texto)), function ($v) {
        return $v !== '';
    }));
}

// 1. Obtener el ID del personaje desde la URL
$id_personaje = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id_personaje <= 0) {
    echo '<div class="container py-5"><div class="alert alert-danger">ID de personaje no válido. <a href="index.php?page=personajes/lista" class="alert-link">Volver al listado</a></div></div>';
    exit;
}

// 2. Consultar el personaje en la base de datos
$stmt = $conexion->prepare("SELECT * FROM personajes WHERE id = ?");
$stmt->bind_param("i", $id_personaje);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    echo '<div class="container py-5"><div class="alert alert-warning">El personaje solicitado no existe o fue eliminado. <a href="index.php?page=personajes/lista" class="alert-link">Volver al listado</a></div></div>';
    exit;
}

$pj = $resultado->fetch_assoc();
$stmt->close();

// 3. Mapear los datos de la BD al formato que esperan tus funciones de cálculo
$datos_hoja = [
    'nivel' => (int) ($pj['nivel'] ?? 1),
    'raza' => (string) ($pj['raza'] ?? ''),
    'subraza' => (string) ($pj['subraza'] ?? ''),
    'clase' => (string) ($pj['clase'] ?? ''),
    'subclase' => (string) ($pj['subclase'] ?? ''),
    'trasfondo' => (string) ($pj['trasfondo'] ?? ''),
    'atributos' => [
        'str' => (int) ($pj['fuerza'] ?? 10),
        'dex' => (int) ($pj['destreza'] ?? 10),
        'con' => (int) ($pj['constitucion'] ?? 10),
        'int' => (int) ($pj['inteligencia'] ?? 10),
        'wis' => (int) ($pj['sabiduria'] ?? 10),
        'cha' => (int) ($pj['carisma'] ?? 10),
    ],
    'salvaciones' => pj_ver_decodificar_lista($pj['salvaciones'] ?? ''),
    'habilidades' => pj_ver_decodificar_lista($pj['habilidades'] ?? ''),
];

if (function_exists('personaje_calcular_bonos_selecciones')) {
    $bonos_sel = personaje_calcular_bonos_selecciones(
        $datos_hoja['raza'] ?? '',
        $datos_hoja['subraza'] ?? '',
        $datos_hoja['clase'] ?? '',
        $datos_hoja['subclase'] ?? '',
        $datos_hoja['nivel'] ?? 1,
        $datos_hoja['trasfondo'] ?? ''
    );

    if (is_array($bonos_sel['atributos'] ?? null)) {
        foreach ($datos_hoja['atributos'] as $cod_a => $val_a) {
            if (isset($bonos_sel['atributos'][$cod_a])) {
                $datos_hoja['atributos'][$cod_a] = (int) $val_a + (int) $bonos_sel['atributos'][$cod_a];
            }
        }
    }
}

// Opcional: si existe la tabla `personaje_equipo`, recuperamos los objetos guardados
$equipo_items = array();
try {
    $chk_pe = $conexion->query("SHOW TABLES LIKE 'personaje_equipo'");
    if ($chk_pe && $chk_pe->num_rows > 0) {
        $stmt_eq = $conexion->prepare("SELECT objeto_slug, cantidad FROM `personaje_equipo` WHERE personaje_id = ?");
        if ($stmt_eq) {
            $stmt_eq->bind_param("i", $id_personaje);
            if ($stmt_eq->execute()) {
                $res_eq = $stmt_eq->get_result();
                while ($row_eq = $res_eq->fetch_assoc()) {
                    $slug_e = trim((string) ($row_eq['objeto_slug'] ?? ''));
                    if ($slug_e !== '') {
                        $cant_e = max(1, (int) ($row_eq['cantidad'] ?? 1));
                        $nom_e = ucwords(str_replace(array('-', '_'), ' ', $slug_e));
                        $equipo_items[] = $cant_e > 1 ? ($nom_e . ' (x' . $cant_e . ')') : $nom_e;
                    }
                }
            }
            $stmt_eq->close();
        }
    }
} catch (mysqli_sql_exception $e) {
    // Si no existe la tabla intermedia, usamos el campo de texto `equipo`
}

$bono_comp = personaje_bono_competencia($datos_hoja['nivel']);
$atributos_info = personaje_atributos();
$habilidades_info = personaje_habilidades();
$destreza_total = $datos_hoja['atributos']['dex'];

// Obtener traits de raza y subraza desde las tablas intermedias
$traits_personaje = array();

if (function_exists('personaje_obtener_traits_entidad')) {
    if (!empty($datos_hoja['raza'])) {
        foreach (personaje_obtener_traits_entidad('raza_trait', 'raza_id', 'razas', 'name', $datos_hoja['raza'], 'Raza') as $tr) {
            $traits_personaje[] = $tr;
        }
    }
    if (!empty($datos_hoja['subraza'])) {
        foreach (personaje_obtener_traits_entidad('subraza_trait', 'subraza_id', 'subrazas', 'name', $datos_hoja['subraza'], 'Subraza') as $tr) {
            $traits_personaje[] = $tr;
        }
    }
}

$notas_texto = trim((string) ($pj['notas'] ?? $pj['rasgos'] ?? ''));
?>

<div class="container py-4">
    <!-- Barra superior de acciones -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="index.php?page=personajes/lista" class="btn btn-outline-success btn-sm fw-bold"
            style="--bs-btn-hover-bg: #198754; --bs-btn-border-color: #75b798; --bs-btn-color: #1e6b48;">
            <i class="bi bi-arrow-left"></i> Volver al listado
        </a>
        <div>
            <button type="button" onclick="imprimirFichaPdf();" class="btn btn-outline-success btn-sm fw-bold"
                style="--bs-btn-hover-bg: #198754; --bs-btn-border-color: #75b798; --bs-btn-color: #1e6b48;">
                🖨️ Imprimir / PDF
            </button>
        </div>
    </div>

    <!-- Ficha de Personaje - Cabecera Principal -->
    <div class="card shadow-sm border-success-subtle mb-4" style="background-color: #fbfdfb;">
        <div class="card-body p-4">
            <div class="row align-items-center">
                <div class="col-md-4 mb-3 mb-md-0">
                    <h1 class="fw-bold text-success mb-1">
                        <?php echo htmlspecialchars($pj['nombre'], ENT_QUOTES, 'UTF-8'); ?>
                    </h1>
                    <p class="text-muted mb-0">
                        Jugador: <strong
                            class="text-dark"><?php echo htmlspecialchars($pj['jugador'], ENT_QUOTES, 'UTF-8'); ?></strong>
                    </p>
                </div>
                <div class="col-md-8">
                    <div class="row text-center g-2">
                        <div class="col border-start border-success-subtle">
                            <span class="d-block text-muted small">CLASE</span>
                            <strong
                                class="fs-6 text-dark"><?php echo htmlspecialchars($pj['clase'], ENT_QUOTES, 'UTF-8'); ?></strong>
                            <?php if (!empty($pj['subclase'])): ?>
                                <div class="text-success small" style="font-size: 0.75rem;">
                                    <?php echo htmlspecialchars($pj['subclase'], ENT_QUOTES, 'UTF-8'); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col border-start border-success-subtle">
                            <span class="d-block text-muted small">RAZA</span>
                            <strong
                                class="fs-6 text-dark"><?php echo htmlspecialchars($pj['raza'], ENT_QUOTES, 'UTF-8'); ?></strong>
                            <?php if (!empty($pj['subraza'])): ?>
                                <div class="text-success small" style="font-size: 0.75rem;">
                                    <?php echo htmlspecialchars($pj['subraza'], ENT_QUOTES, 'UTF-8'); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col border-start border-success-subtle">
                            <span class="d-block text-muted small">TRASFONDO</span>
                            <strong
                                class="fs-6 text-dark"><?php echo htmlspecialchars($pj['trasfondo'], ENT_QUOTES, 'UTF-8'); ?></strong>
                        </div>
                        <div class="col border-start border-success-subtle">
                            <span class="d-block text-muted small">NIVEL / EXP</span>
                            <strong class="fs-6 text-dark">
                                <?php echo (int) $pj['nivel']; ?>
                                <span class="text-muted small">(<?php echo number_format((int) $pj['experiencia']); ?>
                                    XP)</span>
                            </strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Atributos a todo ancho -->
    <div class="card shadow-sm border-success-subtle mb-4" style="background-color: #fbfdfb;">
        <div class="card-header bg-success-subtle text-success fw-bold border-bottom border-success-subtle">
            Atributos
        </div>
        <div class="card-body">
            <div class="row text-center g-3">
                <?php foreach ($atributos_info as $codigo => $info):
                    $slug_attr = strtolower(trim((string) $codigo));
                    $valor_attr = $datos_hoja['atributos'][$codigo] ?? 10;
                    $mod = personaje_modificador($valor_attr);
                    $mod_formateado = personaje_formatear_modificador($mod);
                    ?>
                    <div class="col-6 col-sm-4 col-md-2">
                        <div class="p-2 border border-success-subtle rounded h-100" style="background-color: #eef7f1;">
                            <div class="d-flex align-items-center justify-content-center gap-1 mb-1">
                                <img src="images/<?php echo htmlspecialchars($slug_attr, ENT_QUOTES, 'UTF-8'); ?>.png"
                                    alt="<?php echo htmlspecialchars(strtoupper($slug_attr), ENT_QUOTES, 'UTF-8'); ?>"
                                    style="width: 18px; height: 18px; object-fit: contain; flex-shrink: 0;"
                                    onerror="this.style.display='none';">
                                <span class="text-uppercase small text-success fw-bold">
                                    <?php echo htmlspecialchars(strtoupper($slug_attr), ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                            </div>
                            <span
                                class="fs-3 fw-bold text-dark d-block"><?php echo htmlspecialchars($mod_formateado, ENT_QUOTES, 'UTF-8'); ?></span>
                            <span class="d-block small text-muted">(<?php echo (int) $valor_attr; ?>)</span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Rasgos de Raza y Subraza (Traits) a todo ancho -->
    <?php if (!empty($traits_personaje)): ?>
        <div class="card shadow-sm border-success-subtle mb-4" style="background-color: #fbfdfb;">
            <div
                class="card-header bg-success-subtle text-success fw-bold border-bottom border-success-subtle d-flex justify-content-between align-items-center">
                <span>Rasgos Raciales y de Subraza (Traits)</span>
                <span class="badge text-success border border-success-subtle" style="background-color: #eef7f1;">
                    <?php echo count($traits_personaje); ?>
                </span>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <?php foreach ($traits_personaje as $trait): ?>
                        <div class="col-md-6">
                            <div class="p-3 rounded border border-success-subtle bg-white h-100">
                                <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                                    <strong class="text-success">
                                        <?php echo htmlspecialchars($trait['nombre'], ENT_QUOTES, 'UTF-8'); ?>
                                    </strong>
                                    <?php if (!empty($trait['origen'])): ?>
                                        <span class="badge text-success border border-success-subtle flex-shrink-0"
                                            style="background-color: #eef7f1; font-size: 0.72rem;">
                                            <?php echo htmlspecialchars($trait['origen'], ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <?php if (!empty($trait['descripcion'])): ?>
                                    <p class="text-muted small mb-0" style="white-space: pre-line;">
                                        <?php echo htmlspecialchars(strip_tags($trait['descripcion']), ENT_QUOTES, 'UTF-8'); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Columna Izquierda: Combate y Salud -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm border-success-subtle h-100" style="background-color: #fbfdfb;">
                <div class="card-header bg-success-subtle text-success fw-bold border-bottom border-success-subtle">
                    Combate y Salud
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent">
                            <span class="text-dark">AC</span>
                            <span class="badge bg-success-subtle text-success border border-success-subtle fs-6">
                                <?php echo (int) $pj['clase_armadura']; ?>
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent">
                            <span class="text-dark">Iniciativa</span>
                            <span class="badge bg-success-subtle text-success border border-success-subtle fs-6">
                                <?php echo htmlspecialchars(personaje_formatear_modificador(personaje_modificador($destreza_total)), ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent">
                            <span class="text-dark">Velocidad</span>
                            <span class="badge bg-success-subtle text-success border border-success-subtle fs-6">
                                <?php echo (int) $pj['velocidad']; ?> ft
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent">
                            <span class="text-dark">Puntos de Golpe Máximos</span>
                            <strong class="text-success"><?php echo (int) $pj['puntos_golpe_maximos']; ?></strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent">
                            <span class="text-dark">Puntos de Golpe Actuales</span>
                            <strong class="text-danger"><?php echo (int) $pj['puntos_golpe_actuales']; ?></strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent">
                            <span class="text-dark">Puntos de Golpe Temporales</span>
                            <strong class="text-muted"><?php echo (int) $pj['puntos_golpe_temporales']; ?></strong>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Columna Derecha: Habilidades y Equipo -->
        <div class="col-lg-8">
            <!-- Habilidades -->
            <div class="card shadow-sm border-success-subtle mb-4" style="background-color: #fbfdfb;">
                <div
                    class="card-header bg-success-subtle text-success fw-bold border-bottom border-success-subtle d-flex justify-content-between align-items-center">
                    <span>Habilidades</span>
                    <span class="badge text-success border border-success-subtle" style="background-color: #eef7f1;">
                        Bono Comp: +<?php echo (int) $bono_comp; ?>
                    </span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php foreach ($habilidades_info as $clave => $hab):
                            $mod_hab = personaje_modificador_habilidad($clave, $datos_hoja);
                            $es_comp = in_array($clave, $datos_hoja['habilidades'], true);
                            ?>
                            <div class="col-md-6 mb-2">
                                <div
                                    class="d-flex justify-content-between align-items-center p-2 border rounded <?php echo $es_comp ? 'border-success bg-success-subtle text-success' : 'border-success-subtle bg-white text-dark'; ?>">
                                    <span>
                                        <?php if ($es_comp): ?>
                                            <span class="badge bg-success me-1" title="Competente">●</span>
                                        <?php endif; ?>
                                        <?php echo htmlspecialchars($hab['nombre'], ENT_QUOTES, 'UTF-8'); ?>
                                        <small
                                            class="text-muted">(<?php echo htmlspecialchars(strtoupper($hab['atributo']), ENT_QUOTES, 'UTF-8'); ?>)</small>
                                    </span>
                                    <strong><?php echo htmlspecialchars(personaje_formatear_modificador($mod_hab), ENT_QUOTES, 'UTF-8'); ?></strong>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Equipo y Posesiones -->
            <div class="card shadow-sm border-success-subtle mb-4" style="background-color: #fbfdfb;">
                <div class="card-header bg-success-subtle text-success fw-bold border-bottom border-success-subtle">
                    Equipo y Posesiones
                </div>
                <div class="card-body">
                    <?php if (!empty($equipo_items)): ?>
                        <ul class="list-group list-group-flush small">
                            <?php foreach ($equipo_items as $eq_nom): ?>
                                <li class="list-group-item px-0 py-1 bg-transparent text-dark border-success-subtle">
                                    <?php echo htmlspecialchars($eq_nom, ENT_QUOTES, 'UTF-8'); ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php elseif (!empty($pj['equipo'])): ?>
                        <p class="card-text text-muted mb-0" style="white-space: pre-line;">
                            <?php echo htmlspecialchars($pj['equipo'], ENT_QUOTES, 'UTF-8'); ?>
                        </p>
                    <?php else: ?>
                        <p class="card-text text-muted mb-0">Sin equipo registrado.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Notas abajo de todo -->
    <div class="card shadow-sm border-success-subtle mt-2" style="background-color: #fbfdfb;">
        <div class="card-header bg-success-subtle text-success fw-bold border-bottom border-success-subtle">
            Notas
        </div>
        <div class="card-body">
            <p class="card-text text-muted mb-0" style="white-space: pre-line;">
                <?php echo $notas_texto !== '' ? htmlspecialchars($notas_texto, ENT_QUOTES, 'UTF-8') : 'Sin notas adicionales.'; ?>
            </p>
        </div>
    </div>
</div>

<script>
function imprimirFichaPdf() {
    const tituloOriginal = document.title;

    const limpiarParte = (valor) => {
        return (valor || '')
            .toString()
            .trim()
            .replace(/[\/\\]+/g, '_')        // Convierte / o \ en _
            .replace(/[\x00-\x1f<>:"|?*]+/g, '') // Limpia caracteres prohibidos en nombres de archivo
            .replace(/\s+/g, ' ')
            .trim();
    };

    const nombrePj = limpiarParte(<?php echo json_encode((string) ($pj['nombre'] ?? 'Personaje'), JSON_UNESCAPED_UNICODE); ?>) || 'Personaje';
    const nombreJugador = limpiarParte(<?php echo json_encode((string) ($pj['jugador'] ?? 'Jugador'), JSON_UNESCAPED_UNICODE); ?>) || 'Jugador';

    document.title = `${nombrePj}_${nombreJugador}`;

    const restaurarTitulo = () => {
        document.title = tituloOriginal;
        window.removeEventListener('afterprint', restaurarTitulo);
    };

    window.addEventListener('afterprint', restaurarTitulo);
    window.print();

    // Respaldo por si el navegador no dispara afterprint al cancelar
    setTimeout(restaurarTitulo, 1000);
}
</script>