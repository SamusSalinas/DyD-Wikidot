<?php
// Control de sesión y conexión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../includes/conexion.php';
require_once __DIR__ . '/../../includes/funciones_personaje.php';

// 1. Si llegaron datos del Capítulo 4 por POST, los guardamos en la sesión
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['personaje']) || !is_array($_SESSION['personaje'])) {
        $_SESSION['personaje'] = array();
    }

    foreach ($_POST as $clave => $valor) {
        $_SESSION['personaje'][$clave] = $valor;
    }

    // Si no se marcó ningún checkbox de objetos, aseguramos array vacío
    $objetos_raw = isset($_POST['equipo_objetos']) && is_array($_POST['equipo_objetos'])
        ? $_POST['equipo_objetos']
        : array();

    $objetos_limpios = array();
    foreach ($objetos_raw as $slug_item) {
        $slug_saneado = trim((string) $slug_item);
        if ($slug_saneado !== '' && !in_array($slug_saneado, $objetos_limpios, true)) {
            $objetos_limpios[] = $slug_saneado;
        }
    }
    $_SESSION['personaje']['equipo_objetos'] = $objetos_limpios;

    // Si no viene texto manual en `equipo`, construimos el resumen legible desde los slugs elegidos
    $equipo_manual = isset($_POST['equipo']) ? trim((string) $_POST['equipo']) : '';
    if ($equipo_manual === '') {
        $etiquetas_eq = array_map(function ($slug) {
            return ucwords(str_replace(array('-', '_'), ' ', $slug));
        }, $objetos_limpios);
        $_SESSION['personaje']['equipo'] = implode(', ', $etiquetas_eq);
    }

    if (isset($_POST['rasgos'])) {
        $_SESSION['personaje']['rasgos'] = mb_substr(trim((string) $_POST['rasgos']), 0, 2000, 'UTF-8');
    }
}

// 2. Verificamos que existan datos de personaje en la sesión
if (!isset($_SESSION['personaje'])) {
    // Si no hay nada, redirigimos al inicio del asistente
    header('Location: index.php?page=personajes/hoja&capitulo=1');
    exit;
}

// 3. Validamos y depuramos toda la información acumulada con tu función central
$resultado_validacion = personaje_generar_hoja($_SESSION['personaje']);
$datos_personaje = $resultado_validacion['datos'];
$errores = $resultado_validacion['errores'];

// Si hay errores de validación, los mostramos en pantalla
if (!empty($errores)) {
    ?>
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-4">
            <div class="alert alert-danger">
                <h5 class="alert-heading fw-bold">¡Atención! Hay datos inválidos:</h5>
                <ul class="mb-0">
                    <?php foreach ($errores as $error) { ?>
                        <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                    <?php } ?>
                </ul>
            </div>
            <a href="index.php?page=personajes/hoja&capitulo=1" class="btn btn-secondary mt-3">Volver a revisar el asistente</a>
        </div>
    </div>
    <?php
    exit;
}

// 4. Inserción dinámica y segura en la Base de Datos con MySQLi
try {
    global $conexion;

    $columnas_tabla = array();
    $res_cols = $conexion->query("SHOW COLUMNS FROM `personajes`");
    if ($res_cols) {
        while ($col = $res_cols->fetch_assoc()) {
            $columnas_tabla[] = (string) ($col['Field'] ?? '');
        }
    }

    $attrs = isset($datos_personaje['atributos']) && is_array($datos_personaje['atributos'])
        ? $datos_personaje['atributos']
        : array();

    // Diccionario de posibles columnas en `personajes` => valor validado
    $candidatos = array(
        'nombre'                    => (string) ($datos_personaje['nombre'] ?? ''),
        'nombre_personaje'          => (string) ($datos_personaje['nombre'] ?? ''),
        'jugador'                   => (string) ($datos_personaje['jugador'] ?? ''),
        'raza'                      => (string) ($datos_personaje['raza'] ?? ''),
        'subraza'                   => (string) ($datos_personaje['subraza'] ?? ''),
        'clase'                     => (string) ($datos_personaje['clase'] ?? ''),
        'subclase'                  => (string) ($datos_personaje['subclase'] ?? ''),
        'trasfondo'                 => (string) ($datos_personaje['trasfondo'] ?? ''),
        'alineamiento'              => (string) ($datos_personaje['alineamiento'] ?? ''),
        'nivel'                     => (int) ($datos_personaje['nivel'] ?? 1),
        'experiencia'               => (int) ($datos_personaje['experiencia'] ?? 0),
        'clase_armadura'            => (int) ($datos_personaje['clase_armadura'] ?? 10),
        'velocidad'                 => (int) ($datos_personaje['velocidad'] ?? 30),
        'puntos_golpe_actuales'     => (int) ($datos_personaje['puntos_golpe_actuales'] ?? 10),
        'puntos_golpe_maximos'      => (int) ($datos_personaje['puntos_golpe_maximos'] ?? 10),
        'puntos_golpe_temporales'   => (int) ($datos_personaje['puntos_golpe_temporales'] ?? 0),
        'fuerza'                    => (int) ($attrs['str'] ?? 10),
        'destreza'                  => (int) ($attrs['dex'] ?? 10),
        'constitucion'              => (int) ($attrs['con'] ?? 10),
        'inteligencia'              => (int) ($attrs['int'] ?? 10),
        'sabiduria'                 => (int) ($attrs['wis'] ?? 10),
        'carisma'                   => (int) ($attrs['cha'] ?? 10),
        'habilidades'               => json_encode($datos_personaje['habilidades'] ?? array(), JSON_UNESCAPED_UNICODE),
        'salvaciones'               => json_encode($datos_personaje['salvaciones'] ?? array(), JSON_UNESCAPED_UNICODE),
        'equipo'                    => (string) ($datos_personaje['equipo'] ?? ''),
        'rasgos'                    => (string) ($datos_personaje['rasgos'] ?? ''),
    );

    $cols_insert = array();
    $placeholders = array();
    $types = '';
    $params = array();

    foreach ($candidatos as $col_name => $val) {
        if (in_array($col_name, $columnas_tabla, true)) {
            $cols_insert[] = "`{$col_name}`";
            $placeholders[] = '?';
            $types .= is_int($val) ? 'i' : 's';
            $params[] = $val;
        }
    }

    if (empty($cols_insert)) {
        throw new RuntimeException('No se encontraron columnas compatibles en la tabla personajes.');
    }

    $sql = "INSERT INTO `personajes` (" . implode(', ', $cols_insert) . ") VALUES (" . implode(', ', $placeholders) . ")";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $personaje_id = (int) $conexion->insert_id;
    $stmt->close();

    // Guardamos los objetos elegidos por checkbox en `personaje_equipo` si existe la tabla
    $objetos_a_guardar = isset($datos_personaje['equipo_objetos']) && is_array($datos_personaje['equipo_objetos'])
        ? $datos_personaje['equipo_objetos']
        : array();

    if ($personaje_id > 0 && !empty($objetos_a_guardar)) {
        $check_pe = $conexion->query("SHOW TABLES LIKE 'personaje_equipo'");
        if ($check_pe && $check_pe->num_rows > 0) {
            $stmt_eq = $conexion->prepare(
                "INSERT INTO `personaje_equipo` (personaje_id, objeto_slug, cantidad) VALUES (?, ?, 1)"
            );
            if ($stmt_eq) {
                foreach ($objetos_a_guardar as $slug_eq) {
                    $stmt_eq->bind_param('is', $personaje_id, $slug_eq);
                    $stmt_eq->execute();
                }
                $stmt_eq->close();
            }
        }
    }

    // Limpiamos la sesión del asistente tras el éxito
    unset($_SESSION['personaje']);

    // Mensaje de éxito visual con Bootstrap
    ?>
    <div class="card shadow-sm border-0 mb-4 text-center py-5">
        <div class="card-body p-4">
            <div class="mb-3">
                <span class="display-1 text-success">✅</span>
            </div>
            <h2 class="fw-bold text-success">¡Personaje creado con éxito!</h2>
            <p class="text-muted mb-4">Tu aventurero ha sido registrado correctamente en las crónicas del reino.</p>
            <div class="d-flex justify-content-center gap-2">
                <?php if ($personaje_id > 0) { ?>
                    <a href="index.php?page=personajes/ver&id=<?php echo $personaje_id; ?>" class="btn btn-success px-4 fw-bold">Ver hoja creada</a>
                <?php } ?>
                <a href="index.php?page=personajes/lista" class="btn btn-primary px-4 fw-bold">Ver mis personajes</a>
            </div>
        </div>
    </div>
    <?php

} catch (Throwable $e) {
    echo '<div class="alert alert-danger m-4">Error al guardar en la base de datos: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</div>';
}
?>