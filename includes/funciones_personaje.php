<?php
require_once __DIR__ . '/conexion.php';

function personaje_opciones_clases()
{
    global $conexion;
    $resultado = $conexion->query('SELECT nombre FROM clases ORDER BY nombre ASC');
    $opciones = array();
    while ($fila = $resultado->fetch_assoc()) {
        $opciones[] = $fila['nombre'];
    }

    return $opciones;
}

function personaje_opciones_razas()
{
    global $conexion;
    $resultado = $conexion->query('SELECT name FROM razas ORDER BY name ASC');
    $opciones = array();
    while ($fila = $resultado->fetch_assoc()) {
        $opciones[] = $fila['name'];
    }

    return $opciones;
}

function personaje_opciones_trasfondos()
{
    global $conexion;
    $resultado = $conexion->query('SELECT name FROM trasfondos ORDER BY name ASC');
    $opciones = array();
    while ($fila = $resultado->fetch_assoc()) {
        $opciones[] = $fila['name'];
    }

    return $opciones;
}

function personaje_opciones_subclases($nombre_clase = '')
{
    if ($nombre_clase === '') {
        return array();
    }

    global $conexion;
    $consulta = $conexion->prepare(
        'SELECT s.nombre
         FROM subclases s
         INNER JOIN clases c ON c.id = s.clase_id
         WHERE c.nombre = ?
         ORDER BY s.nombre ASC'
    );
    $consulta->bind_param('s', $nombre_clase);
    $consulta->execute();
    $consulta->bind_result($nombre);

    $opciones = array();
    while ($consulta->fetch()) {
        $opciones[] = $nombre;
    }
    $consulta->close();

    return $opciones;
}

function personaje_opciones_subrazas($name_raza = '')
{
    global $conexion;
    $subrazas = array();
    if (empty($name_raza) || !isset($conexion)) {
        return $subrazas;
    }

    $stmt = $conexion->prepare(
        'SELECT s.name
         FROM subrazas s
         INNER JOIN razas r ON s.raza_id = r.id
         WHERE r.name = ?
         ORDER BY s.name ASC'
    );
    if ($stmt) {
        $stmt->bind_param('s', $name_raza);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $subrazas[] = $row['name'];
        }
        $stmt->close();
    }

    return $subrazas;
}

function personaje_atributos()
{
    return array(
        'str' => array('nombre' => 'Fuerza', 'campo' => 'fuerza'),
        'dex' => array('nombre' => 'Destreza', 'campo' => 'destreza'),
        'con' => array('nombre' => 'Constitución', 'campo' => 'constitucion'),
        'int' => array('nombre' => 'Inteligencia', 'campo' => 'inteligencia'),
        'wis' => array('nombre' => 'Sabiduría', 'campo' => 'sabiduria'),
        'cha' => array('nombre' => 'Carisma', 'campo' => 'carisma')
    );
}

function personaje_habilidades()
{
    return array(
        'acrobatics' => array('nombre' => 'Acrobacias', 'atributo' => 'dex'),
        'animal_handling' => array('nombre' => 'Trato con animales', 'atributo' => 'wis'),
        'arcana' => array('nombre' => 'Arcanos', 'atributo' => 'int'),
        'athletics' => array('nombre' => 'Atletismo', 'atributo' => 'str'),
        'deception' => array('nombre' => 'Engaño', 'atributo' => 'cha'),
        'history' => array('nombre' => 'Historia', 'atributo' => 'int'),
        'insight' => array('nombre' => 'Perspicacia', 'atributo' => 'wis'),
        'intimidation' => array('nombre' => 'Intimidación', 'atributo' => 'cha'),
        'investigation' => array('nombre' => 'Investigación', 'atributo' => 'int'),
        'medicine' => array('nombre' => 'Medicina', 'atributo' => 'wis'),
        'nature' => array('nombre' => 'Naturaleza', 'atributo' => 'int'),
        'perception' => array('nombre' => 'Percepción', 'atributo' => 'wis'),
        'performance' => array('nombre' => 'Interpretación', 'atributo' => 'cha'),
        'persuasion' => array('nombre' => 'Persuasión', 'atributo' => 'cha'),
        'religion' => array('nombre' => 'Religión', 'atributo' => 'int'),
        'sleight_of_hand' => array('nombre' => 'Juego de manos', 'atributo' => 'dex'),
        'stealth' => array('nombre' => 'Sigilo', 'atributo' => 'dex'),
        'survival' => array('nombre' => 'Supervivencia', 'atributo' => 'wis')
    );
}

function personaje_modificador($puntuacion)
{
    return (int) floor(((int) $puntuacion - 10) / 2);
}

function personaje_formatear_modificador($modificador)
{
    return ((int) $modificador >= 0 ? '+' : '') . (int) $modificador;
}

function personaje_bono_competencia($nivel)
{
    $nivel = max(1, min(20, (int) $nivel));
    return 2 + (int) floor(($nivel - 1) / 4);
}

function personaje_valor_texto($entrada, $campo, $longitud_maxima, &$errores)
{
    if (!isset($entrada[$campo])) {
        return '';
    }

    if (!is_string($entrada[$campo])) {
        $errores[] = 'El campo "' . $campo . '" no es válido.';
        return '';
    }

    $valor = trim($entrada[$campo]);
    if (strlen($valor) > $longitud_maxima) {
        $errores[] = 'El campo "' . $campo . '" supera el largo máximo permitido.';
        return substr($valor, 0, $longitud_maxima);
    }

    return $valor;
}

function personaje_valor_entero($entrada, $campo, $predeterminado, $minimo, $maximo, &$errores)
{
    if (!isset($entrada[$campo]) || $entrada[$campo] === '') {
        return $predeterminado;
    }

    if (!is_string($entrada[$campo]) && !is_int($entrada[$campo])) {
        $errores[] = 'El campo "' . $campo . '" debe ser un número válido.';
        return $predeterminado;
    }

    $valor = filter_var($entrada[$campo], FILTER_VALIDATE_INT);
    if ($valor === false || $valor < $minimo || $valor > $maximo) {
        $errores[] = 'El campo "' . $campo . '" debe ser un número entre ' . $minimo . ' y ' . $maximo . '.';
        return $predeterminado;
    }

    return $valor;
}

function personaje_valores_seleccionados($entrada, $campo, $opciones, &$errores)
{
    if (!isset($entrada[$campo])) {
        return array();
    }

    if (!is_array($entrada[$campo])) {
        $errores[] = 'La selección de "' . $campo . '" no es válida.';
        return array();
    }

    $seleccionados = array();
    foreach ($entrada[$campo] as $valor) {
        if (!is_string($valor) || !in_array($valor, $opciones, true)) {
            $errores[] = 'La selección de "' . $campo . '" contiene un valor no permitido.';
            continue;
        }
        if (!in_array($valor, $seleccionados, true)) {
            $seleccionados[] = $valor;
        }
    }

    return $seleccionados;
}


function personaje_generar_hoja($entrada, $clases = null, $razas = null, $trasfondos = null, $subclases = null, $subrazas = null)
{
    $errores = array();
    if (!is_array($entrada)) {
        $entrada = array();
        $errores[] = 'Los datos recibidos para la hoja no son válidos.';
    }

    $clases = $clases === null ? personaje_opciones_clases() : $clases;
    $razas = $razas === null ? personaje_opciones_razas() : $razas;
    if ($subrazas === null) {
        $raza_actual = $entrada['raza'] ?? '';
        $subrazas = personaje_opciones_subrazas($raza_actual);
    }
    $trasfondos = $trasfondos === null ? personaje_opciones_trasfondos() : $trasfondos;
    if ($subclases === null) {
        $clase_actual = $entrada['clase'] ?? '';
        $subclases = personaje_opciones_subclases($clase_actual);
    }

    $habilidades = personaje_habilidades();
    $atributos = personaje_atributos();

    $datos = array(
        'nombre' => personaje_valor_texto($entrada, 'nombre', 80, $errores),
        'jugador' => personaje_valor_texto($entrada, 'jugador', 80, $errores),
        'trasfondo' => '',
        'subclase' => '',
        'subraza' => '',
        'alineamiento' => personaje_valor_texto($entrada, 'alineamiento', 40, $errores),
        'clase' => '',
        'raza' => '',
        'nivel' => personaje_valor_entero($entrada, 'nivel', 1, 1, 20, $errores),
        'experiencia' => personaje_valor_entero($entrada, 'experiencia', 0, 0, 9999999, $errores),
        'clase_armadura' => personaje_valor_entero($entrada, 'clase_armadura', 10, 0, 99, $errores),
        'velocidad' => personaje_valor_entero($entrada, 'velocidad', 30, 0, 999, $errores),
        'puntos_golpe_maximos' => personaje_valor_entero($entrada, 'puntos_golpe_maximos', 10, 0, 9999, $errores),
        'puntos_golpe_actuales' => personaje_valor_entero($entrada, 'puntos_golpe_actuales', 10, 0, 9999, $errores),
        'puntos_golpe_temporales' => personaje_valor_entero($entrada, 'puntos_golpe_temporales', 0, 0, 9999, $errores),
        'atributos' => array(),
        'salvaciones' => array(),
        'habilidades' => array(),
        'equipo_objetos' => array(),
        'equipo' => personaje_valor_texto($entrada, 'equipo', 2000, $errores),
        'rasgos' => personaje_valor_texto($entrada, 'rasgos', 2000, $errores)
    );

    if (isset($entrada['clase']) && is_string($entrada['clase']) && in_array($entrada['clase'], $clases, true)) {
        $datos['clase'] = $entrada['clase'];
    } elseif (isset($entrada['clase']) && $entrada['clase'] !== '') {
        $errores[] = 'Selecciona una clase válida.';
    }

    if (isset($entrada['raza']) && is_string($entrada['raza']) && in_array($entrada['raza'], $razas, true)) {
        $datos['raza'] = $entrada['raza'];
    } elseif (isset($entrada['raza']) && $entrada['raza'] !== '') {
        $errores[] = 'Selecciona una raza válida.';
    }

    $trasfondo = personaje_valor_texto($entrada, 'trasfondo', 80, $errores);
    if ($trasfondo !== '' && in_array($trasfondo, $trasfondos, true)) {
        $datos['trasfondo'] = $trasfondo;
    } elseif ($trasfondo !== '') {
        $errores[] = 'Selecciona un trasfondo válido.';
    }

    $subclase = personaje_valor_texto($entrada, 'subclase', 80, $errores);
    if ($subclase !== '' && in_array($subclase, $subclases, true)) {
        $datos['subclase'] = $subclase;
    } elseif ($subclase !== '') {
        $errores[] = 'Selecciona una subclase válida para la clase elegida.';
    }

    $subraza = personaje_valor_texto($entrada, 'subraza', 200, $errores);
    if ($subraza !== '' && in_array($subraza, $subrazas, true)) {
        $datos['subraza'] = $subraza;
    } elseif ($subraza !== '') {
        $errores[] = 'Selecciona una subraza válida para la raza elegida.';
    }

    foreach ($atributos as $codigo => $atributo) {
        $campo = $atributo['campo'];
        $datos['atributos'][$codigo] = personaje_valor_entero($entrada, $campo, 10, 1, 30, $errores);
    }

    $datos['salvaciones'] = personaje_valores_seleccionados(
        $entrada,
        'salvaciones',
        array_keys($atributos),
        $errores
    );
    $datos['habilidades'] = personaje_valores_seleccionados(
        $entrada,
        'habilidades',
        array_keys($habilidades),
        $errores
    );

    // Validar y depurar objetos de equipo seleccionados por checkbox (`equipo_objetos[]`)
    if (isset($entrada['equipo_objetos']) && is_array($entrada['equipo_objetos'])) {
        $catalogo_objetos = function_exists('personaje_obtener_todos_objetos_equipo')
            ? personaje_obtener_todos_objetos_equipo()
            : array();
        $slugs_validos = array_keys($catalogo_objetos);
        $objetos_filtrados = array();

        foreach ($entrada['equipo_objetos'] as $slug_item) {
            if (!is_string($slug_item) && !is_numeric($slug_item)) {
                continue;
            }
            $slug_limpio = trim((string) $slug_item);
            if ($slug_limpio === '') {
                continue;
            }
            if (!empty($slugs_validos) && !in_array($slug_limpio, $slugs_validos, true)) {
                $errores[] = 'Uno de los objetos de equipo seleccionados no es válido.';
                continue;
            }
            if (!in_array($slug_limpio, $objetos_filtrados, true)) {
                $objetos_filtrados[] = $slug_limpio;
            }
        }

        $datos['equipo_objetos'] = $objetos_filtrados;
    }

    // Si `equipo` no vino escrito manualmente, armarlo a partir de los objetos seleccionados
    if ($datos['equipo'] === '' && !empty($datos['equipo_objetos'])) {
        $catalogo_objetos = function_exists('personaje_obtener_todos_objetos_equipo')
            ? personaje_obtener_todos_objetos_equipo()
            : array();
        $nombres_eq = array();
        foreach ($datos['equipo_objetos'] as $slug_eq) {
            $nombres_eq[] = $catalogo_objetos[$slug_eq] ?? ucwords(str_replace(array('-', '_'), ' ', $slug_eq));
        }
        $datos['equipo'] = mb_substr(implode(', ', $nombres_eq), 0, 2000, 'UTF-8');
    }

    if ($datos['puntos_golpe_actuales'] > $datos['puntos_golpe_maximos']) {
        $errores[] = 'Los puntos de golpe actuales no pueden superar el máximo.';
    }

    return array('datos' => $datos, 'errores' => $errores);
}

function personaje_modificador_habilidad($clave, $datos)
{
    $habilidades = personaje_habilidades();
    if (!isset($habilidades[$clave])) {
        return null;
    }

    $atributo = $habilidades[$clave]['atributo'];
    $modificador = personaje_modificador($datos['atributos'][$atributo]);
    if (in_array($clave, $datos['habilidades'], true)) {
        $modificador += personaje_bono_competencia($datos['nivel']);
    }

    return $modificador;
}

function personaje_parsear_salvaciones_es($texto_salvaciones)
{
    $mapa = array(
        'fuerza' => 'str',
        'str' => 'str',
        'strength' => 'str',
        'destreza' => 'dex',
        'dex' => 'dex',
        'dexterity' => 'dex',
        'constitucion' => 'con',
        'con' => 'con',
        'constitution' => 'con',
        'inteligencia' => 'int',
        'int' => 'int',
        'intelligence' => 'int',
        'sabiduria' => 'wis',
        'wis' => 'wis',
        'wisdom' => 'wis',
        'carisma' => 'cha',
        'cha' => 'cha',
        'charisma' => 'cha',
    );

    $codigos = array();
    $partes = explode(',', (string) $texto_salvaciones);

    foreach ($partes as $parte) {
        $limpia = trim(mb_strtolower($parte, 'UTF-8'));
        $limpia = strtr($limpia, array(
            'á' => 'a',
            'é' => 'e',
            'í' => 'i',
            'ó' => 'o',
            'ú' => 'u',
        ));

        if ($limpia !== '' && isset($mapa[$limpia])) {
            $cod = $mapa[$limpia];
            if (!in_array($cod, $codigos, true)) {
                $codigos[] = $cod;
            }
        }
    }

    return $codigos;
}



function personaje_acumular_bonos_tabla($conexion, $tabla_intermedia, $columna_fk, $entidad_id, $etiqueta_origen, array &$bonos_atributos, array &$detalle_bonos)
{
    $entidad_id = (int) $entidad_id;
    if (!$conexion || $entidad_id <= 0) {
        return;
    }

    // Solo tablas con bonos de atributos: raza_bonus, subraza_bonus y trasfondo_bonus (+1 directo)
    $config_tablas = array(
        'raza_bonus' => array('fk' => 'raza_id', 'col_valor' => 'valor'),
        'subraza_bonus' => array('fk' => 'subraza_id', 'col_valor' => 'valor'),
        'trasfondo_bonus' => array('fk' => 'trasfondo_id', 'col_valor' => null),
    );

    if (!isset($config_tablas[$tabla_intermedia]) || $config_tablas[$tabla_intermedia]['fk'] !== $columna_fk) {
        return;
    }

    $tabla_esc = $conexion->real_escape_string($tabla_intermedia);

    try {
        $check_tbl = $conexion->query("SHOW TABLES LIKE '{$tabla_esc}'");
        if (!$check_tbl || $check_tbl->num_rows === 0) {
            return;
        }
    } catch (mysqli_sql_exception $e) {
        return;
    }

    $col_valor = $config_tablas[$tabla_intermedia]['col_valor'];

    if ($col_valor === null) {
        $sql = "SELECT a.index_api, 1 AS valor
                FROM `{$tabla_esc}` tb
                INNER JOIN ability_scores a ON tb.ability_score_id = a.id
                WHERE tb.`{$columna_fk}` = ?";
    } else {
        $col_esc = $conexion->real_escape_string($col_valor);
        try {
            $check_col = $conexion->query("SHOW COLUMNS FROM `{$tabla_esc}` LIKE '{$col_esc}'");
            if (!$check_col || $check_col->num_rows === 0) {
                return;
            }
        } catch (mysqli_sql_exception $e) {
            return;
        }

        $sql = "SELECT a.index_api, tb.`{$col_esc}` AS valor
                FROM `{$tabla_esc}` tb
                INNER JOIN ability_scores a ON tb.ability_score_id = a.id
                WHERE tb.`{$columna_fk}` = ?";
    }

    try {
        $stmt = $conexion->prepare($sql);
        if (!$stmt) {
            return;
        }

        $stmt->bind_param('i', $entidad_id);
        $stmt->execute();
        $res = $stmt->get_result();

        while ($fila = $res->fetch_assoc()) {
            $cod = strtolower(trim((string) ($fila['index_api'] ?? '')));
            $val = (int) ($fila['valor'] ?? 0);

            if (isset($bonos_atributos[$cod]) && $val !== 0) {
                $bonos_atributos[$cod] += $val;
                $signo = $val >= 0 ? '+' : '';
                $detalle_bonos[] = strtoupper($cod) . ' ' . $signo . $val . ' (' . $etiqueta_origen . ')';
            }
        }

        $stmt->close();
    } catch (mysqli_sql_exception $e) {
        return;
    }
}

function personaje_calcular_bonos_selecciones($raza_sel = '', $subraza_sel = '', $clase_sel = '', $subclase_sel = '', $nivel = 1, $trasfondo_sel = '')
{
    global $conexion;

    $bonos_atributos = array(
        'str' => 0,
        'dex' => 0,
        'con' => 0,
        'int' => 0,
        'wis' => 0,
        'cha' => 0,
    );
    $detalle_bonos = array();
    $salvaciones_clase = array();
    $velocidad = 30;
    $dado_golpe = 8;
    $nivel = max(1, min(20, (int) $nivel));



    foreach (array('raza_sel' => $raza_sel, 'subraza_sel' => $subraza_sel, 'clase_sel' => $clase_sel, 'subclase_sel' => $subclase_sel, 'nivel' => $nivel, 'trasfondo_sel' => $trasfondo_sel) as $nombre_param => $valor_param) {
        if (is_array($valor_param)) {
            $traza = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 4);
            echo '<div class="alert alert-warning small">Param <strong>' . htmlspecialchars($nombre_param) . '</strong> llegó como array desde: ';
            foreach ($traza as $paso) {
                if (!empty($paso['file']) && strpos($paso['file'], 'funciones_personaje.php') === false) {
                    echo htmlspecialchars(basename($paso['file']) . ':' . ($paso['line'] ?? '?')) . ' -> ';
                }
            }
            echo '</div>';
        }
    }


    $raza_sel = trim((string) $raza_sel);
    $subraza_sel = trim((string) $subraza_sel);
    $clase_sel = trim((string) $clase_sel);
    $subclase_sel = trim((string) $subclase_sel);
    $trasfondo_sel = trim((string) $trasfondo_sel);

    if (!isset($conexion) || !$conexion) {
        return array(
            'atributos' => $bonos_atributos,
            'detalle' => $detalle_bonos,
            'salvaciones' => $salvaciones_clase,
            'velocidad' => $velocidad,
            'dado_golpe' => $dado_golpe,
            'nivel' => $nivel,
        );
    }

    // 1. Raza: velocidad y bonos en raza_bonus
    if ($raza_sel !== '') {
        $stmt = $conexion->prepare('SELECT id, name, speed FROM razas WHERE name = ? LIMIT 1');
        if ($stmt) {
            $stmt->bind_param('s', $raza_sel);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($raza = $res->fetch_assoc()) {
                if (!empty($raza['speed'])) {
                    $velocidad = (int) $raza['speed'];
                }
                personaje_acumular_bonos_tabla(
                    $conexion,
                    'raza_bonus',
                    'raza_id',
                    (int) $raza['id'],
                    (string) $raza['name'],
                    $bonos_atributos,
                    $detalle_bonos
                );
            }
            $stmt->close();
        }
    }

    // 2. Subraza: bonos en subraza_bonus (si existe) y velocidad si aplica
    if ($subraza_sel !== '') {
        $stmt = $conexion->prepare('SELECT id, name, speed FROM subrazas WHERE name = ? LIMIT 1');
        if ($stmt) {
            $stmt->bind_param('s', $subraza_sel);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($subraza = $res->fetch_assoc()) {
                if (!empty($subraza['speed']) && (int) $subraza['speed'] > 0) {
                    $velocidad = (int) $subraza['speed'];
                }
                personaje_acumular_bonos_tabla(
                    $conexion,
                    'subraza_bonus',
                    'subraza_id',
                    (int) $subraza['id'],
                    (string) $subraza['name'],
                    $bonos_atributos,
                    $detalle_bonos
                );
            }
            $stmt->close();
        }
    }

    // 3. Trasfondo: bonos en trasfondo_bonus (+1 directo por fila)
    if ($trasfondo_sel !== '') {
        $stmt = $conexion->prepare('SELECT id, name FROM trasfondos WHERE name = ? LIMIT 1');
        if ($stmt) {
            $stmt->bind_param('s', $trasfondo_sel);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($trasfondo = $res->fetch_assoc()) {
                personaje_acumular_bonos_tabla(
                    $conexion,
                    'trasfondo_bonus',
                    'trasfondo_id',
                    (int) $trasfondo['id'],
                    (string) $trasfondo['name'],
                    $bonos_atributos,
                    $detalle_bonos
                );
            }
            $stmt->close();
        }
    }

    // 4. Clase: dado_golpe y salvaciones competentes (sin bonos de atributos)
    if ($clase_sel !== '') {
        $stmt = $conexion->prepare('SELECT id, nombre, dado_golpe, salvaciones FROM clases WHERE nombre = ? LIMIT 1');
        if ($stmt) {
            $stmt->bind_param('s', $clase_sel);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($clase = $res->fetch_assoc()) {
                $dg = (int) preg_replace('/\D+/', '', (string) ($clase['dado_golpe'] ?? ''));
                if ($dg > 0) {
                    $dado_golpe = $dg;
                }

                $salvaciones_clase = personaje_parsear_salvaciones_es($clase['salvaciones'] ?? '');
                if (!empty($salvaciones_clase)) {
                    $codigos_mayus = array_map('strtoupper', $salvaciones_clase);
                    $detalle_bonos[] = 'Salvaciones: ' . implode(', ', $codigos_mayus) . ' (' . $clase['nombre'] . ')';
                }
            }
            $stmt->close();
        }
    }

    return array(
        'atributos' => $bonos_atributos,
        'detalle' => $detalle_bonos,
        'salvaciones' => $salvaciones_clase,
        'velocidad' => $velocidad,
        'dado_golpe' => $dado_golpe,
        'nivel' => $nivel,
    );
}



function personaje_aplicar_bonos_capitulo_2(array &$personaje, $base_puntaje = 10)
{
    $normalizar_texto = static function ($valor) {
        if (is_array($valor)) {
            if (isset($valor['nombre']) && is_scalar($valor['nombre'])) {
                $valor = $valor['nombre'];
            } elseif (isset($valor['name']) && is_scalar($valor['name'])) {
                $valor = $valor['name'];
            } else {
                $primero = reset($valor);
                $valor = is_scalar($primero) ? $primero : '';
            }
        }
        return is_scalar($valor) ? trim((string) $valor) : '';
    };

    $raza_sel = $normalizar_texto($personaje['raza'] ?? '');
    $subraza_sel = $normalizar_texto($personaje['subraza'] ?? '');
    $trasfondo_sel = $normalizar_texto($personaje['trasfondo'] ?? '');
    $clase_sel = $normalizar_texto($personaje['clase'] ?? '');
    $subclase_sel = $normalizar_texto($personaje['subclase'] ?? '');

    $nivel_raw = $personaje['nivel'] ?? 1;
    if (is_array($nivel_raw)) {
        $nivel_raw = reset($nivel_raw);
    }
    $nivel_sel = (int) $nivel_raw;

    if (is_array($base_puntaje)) {
        $base_puntaje = reset($base_puntaje);
    }
    $base_puntaje = (int) $base_puntaje;

    $info_bonos = personaje_calcular_bonos_selecciones(
        $raza_sel,
        $subraza_sel,
        $clase_sel,
        $subclase_sel,
        $nivel_sel,
        $trasfondo_sel
    );

    $firma_actual = implode('|', array(
        $raza_sel,
        $subraza_sel,
        $trasfondo_sel,
        $clase_sel,
        $subclase_sel,
        (int) $info_bonos['nivel'],
        $base_puntaje,
    ));

    if (($personaje['_firma_bonos_cap2'] ?? '') !== $firma_actual) {
        $codigos = array('str', 'dex', 'con', 'int', 'wis', 'cha');
        $atributos_actualizados = array();
        $mapa_atributos = function_exists('personaje_atributos') ? personaje_atributos() : array();

        foreach ($codigos as $cod) {
            $bono = (int) ($info_bonos['atributos'][$cod] ?? 0);
            $valor_final = max(1, min(30, $base_puntaje + $bono));
            $atributos_actualizados[$cod] = $valor_final;

            if (isset($mapa_atributos[$cod]['campo'])) {
                $personaje[$mapa_atributos[$cod]['campo']] = $valor_final;
            }
        }

        $personaje['atributos'] = $atributos_actualizados;
        $personaje['salvaciones'] = is_array($info_bonos['salvaciones']) ? $info_bonos['salvaciones'] : array();
        $personaje['velocidad'] = (int) $info_bonos['velocidad'];

        $mod_dex = personaje_modificador($personaje['atributos']['dex']);
        $mod_con = personaje_modificador($personaje['atributos']['con']);

        $dg = (int) $info_bonos['dado_golpe'];
        $nivel = (int) $info_bonos['nivel'];
        $pg_nivel_1 = max(1, $dg + $mod_con);
        if ($nivel > 1) {
            $promedio_dado = (int) floor($dg / 2) + 1;
            $pg_extra = ($nivel - 1) * max(1, $promedio_dado + $mod_con);
            $pg_total = max(1, $pg_nivel_1 + $pg_extra);
        } else {
            $pg_total = $pg_nivel_1;
        }

        $personaje['puntos_golpe_maximos'] = $pg_total;
        $personaje['puntos_golpe_actuales'] = $pg_total;

        $ca_base = 10 + $mod_dex;
        $clase_norm = strtr(mb_strtolower($clase_sel, 'UTF-8'), array('á' => 'a'));
        if ($clase_norm === 'barbaro') {
            $ca_base += $mod_con;
        }
        $personaje['clase_armadura'] = max(0, $ca_base);

        $personaje['_firma_bonos_cap2'] = $firma_actual;
    }

    return $info_bonos;
}

/**
 * Convierte nombres de proficiencias (en español o inglés, con o sin prefijos como "Skill: ")
 * a las claves oficiales de `personaje_habilidades()`.
 */
function personaje_habilidades_desde_proficiencias(array $nombres_proficiencias)
{
    $mapa_habilidades = array(
        // Inglés (API)
        'acrobatics' => 'acrobatics',
        'animal handling' => 'animal_handling',
        'arcana' => 'arcana',
        'athletics' => 'athletics',
        'deception' => 'deception',
        'history' => 'history',
        'insight' => 'insight',
        'intimidation' => 'intimidation',
        'investigation' => 'investigation',
        'medicine' => 'medicine',
        'nature' => 'nature',
        'perception' => 'perception',
        'performance' => 'performance',
        'persuasion' => 'persuasion',
        'religion' => 'religion',
        'sleight of hand' => 'sleight_of_hand',
        'stealth' => 'stealth',
        'survival' => 'survival',

        // Español
        'acrobacias' => 'acrobatics',
        'trato con animales' => 'animal_handling',
        'arcanos' => 'arcana',
        'conocimiento arcano' => 'arcana',
        'atletismo' => 'athletics',
        'engano' => 'deception',
        'historia' => 'history',
        'perspicacia' => 'insight',
        'intimidacion' => 'intimidation',
        'investigacion' => 'investigation',
        'medicina' => 'medicine',
        'naturaleza' => 'nature',
        'percepcion' => 'perception',
        'interpretacion' => 'performance',
        'persuasion' => 'persuasion',
        'religion' => 'religion',
        'juego de manos' => 'sleight_of_hand',
        'sigilo' => 'stealth',
        'supervivencia' => 'survival',
    );

    $claves = array();
    foreach ($nombres_proficiencias as $nombre) {
        $limpia = trim(mb_strtolower((string) $nombre, 'UTF-8'));
        $limpia = preg_replace('/^(skill|habilidad|tool|herramienta|weapon|arma)\s*:\s*/iu', '', $limpia);
        $limpia = trim(strtr($limpia, array(
            'á' => 'a',
            'é' => 'e',
            'í' => 'i',
            'ó' => 'o',
            'ú' => 'u',
        )));

        if ($limpia !== '' && isset($mapa_habilidades[$limpia])) {
            $clave = $mapa_habilidades[$limpia];
            if (!in_array($clave, $claves, true)) {
                $claves[] = $clave;
            }
        }
    }

    return $claves;
}



function personaje_consultar_proficiencias_entidad(mysqli $conexion, $tabla_intermedia, $columna_fk, $tabla_entidad, $columna_nombre, $valor_nombre)
{
    $proficiencias = array();
    $valor_nombre = trim((string) $valor_nombre);
    if ($valor_nombre === '') {
        return $proficiencias;
    }

    $tabla_int_esc = $conexion->real_escape_string($tabla_intermedia);
    $tabla_ent_esc = $conexion->real_escape_string($tabla_entidad);
    $col_fk_esc = $conexion->real_escape_string($columna_fk);
    $col_nom_esc = $conexion->real_escape_string($columna_nombre);

    try {
        $check_tbl = $conexion->query("SHOW TABLES LIKE '{$tabla_int_esc}'");
        if (!$check_tbl || $check_tbl->num_rows === 0) {
            return $proficiencias;
        }

        // Detecta si la tabla `proficiencias` usa `nombre` o `name`
        $col_prof = 'nombre';
        $check_col_nombre = $conexion->query("SHOW COLUMNS FROM `proficiencias` LIKE 'nombre'");
        if (!$check_col_nombre || $check_col_nombre->num_rows === 0) {
            $check_col_name = $conexion->query("SHOW COLUMNS FROM `proficiencias` LIKE 'name'");
            if ($check_col_name && $check_col_name->num_rows > 0) {
                $col_prof = 'name';
            }
        }

        $sql = "SELECT p.`{$col_prof}` AS prof_nombre
                FROM `{$tabla_int_esc}` ti
                INNER JOIN `proficiencias` p ON ti.proficiencia_id = p.id
                INNER JOIN `{$tabla_ent_esc}` e ON ti.`{$col_fk_esc}` = e.id
                WHERE e.`{$col_nom_esc}` = ?";

        $stmt = $conexion->prepare($sql);
        if ($stmt) {
            $stmt->bind_param('s', $valor_nombre);
            if ($stmt->execute()) {
                $res = $stmt->get_result();
                while ($fila = $res->fetch_assoc()) {
                    $nombre_p = trim((string) ($fila['prof_nombre'] ?? ''));
                    if ($nombre_p !== '' && !in_array($nombre_p, $proficiencias, true)) {
                        $proficiencias[] = $nombre_p;
                    }
                }
            }
            $stmt->close();
        }
    } catch (mysqli_sql_exception $e) {
        return $proficiencias;
    }

    return $proficiencias;
}

function personaje_aplicar_proficiencias_capitulo_3(array &$personaje)
{
    global $conexion;

    $trasfondo_sel = trim((string) ($personaje['trasfondo'] ?? ''));
    $clase_sel = trim((string) ($personaje['clase'] ?? ''));
    $subclase_sel = trim((string) ($personaje['subclase'] ?? ''));

    $firma_actual = $trasfondo_sel . '|' . $clase_sel . '|' . $subclase_sel;

    $prof_trasfondo = array();
    $prof_clase = array();
    $prof_subclase = array();

    if (isset($conexion) && $conexion) {
        if ($trasfondo_sel !== '') {
            $prof_trasfondo = personaje_consultar_proficiencias_entidad(
                $conexion,
                'trasfondo_proficiencias',
                'trasfondo_id',
                'trasfondos',
                'name',
                $trasfondo_sel
            );
        }

        if ($clase_sel !== '') {
            $prof_clase = personaje_consultar_proficiencias_entidad(
                $conexion,
                'clase_proficiencia',
                'clase_id',
                'clases',
                'nombre',
                $clase_sel
            );
        }

        if ($subclase_sel !== '') {
            foreach (array('subclase_proficiencia', 'subclase_proficiencias') as $tabla_sub) {
                $encontradas = personaje_consultar_proficiencias_entidad(
                    $conexion,
                    $tabla_sub,
                    'subclase_id',
                    'subclases',
                    'nombre',
                    $subclase_sel
                );
                if (!empty($encontradas)) {
                    $prof_subclase = $encontradas;
                    break;
                }
            }
        }
    }

    $todas_proficiencias = array_values(array_unique(array_merge(
        $prof_trasfondo,
        $prof_clase,
        $prof_subclase
    )));

    $habilidades_sugeridas = personaje_habilidades_desde_proficiencias($todas_proficiencias);

    if (($personaje['_firma_prof_cap3'] ?? '') !== $firma_actual) {
        $actuales = is_array($personaje['habilidades'] ?? null) ? $personaje['habilidades'] : array();
        foreach ($habilidades_sugeridas as $hab) {
            if (!in_array($hab, $actuales, true)) {
                $actuales[] = $hab;
            }
        }
        $personaje['habilidades'] = $actuales;
        $personaje['_firma_prof_cap3'] = $firma_actual;
    }

    return array(
        'proficiencias' => $todas_proficiencias,
        'proficiencias_trasfondo' => $prof_trasfondo,
        'proficiencias_clase' => $prof_clase,
        'proficiencias_subclase' => $prof_subclase,
        'habilidades' => $habilidades_sugeridas,
    );
}



function personaje_obtener_equipo_clase($clase_sel)
{
    global $conexion;
    $clase_sel = trim((string) $clase_sel);
    $items = array();

    if ($clase_sel === '' || !isset($conexion) || !$conexion) {
        return $items;
    }

    try {
        $check_tbl = $conexion->query("SHOW TABLES LIKE 'clase_equipo'");
        if ($check_tbl && $check_tbl->num_rows > 0) {
            $sql = "SELECT ce.objeto_slug, ce.cantidad
                    FROM `clase_equipo` ce
                    INNER JOIN `clases` c ON ce.clase_id = c.id
                    WHERE c.nombre = ?";

            $stmt = $conexion->prepare($sql);
            if ($stmt) {
                $stmt->bind_param('s', $clase_sel);
                if ($stmt->execute()) {
                    $res = $stmt->get_result();
                    while ($fila = $res->fetch_assoc()) {
                        $slug = trim((string) ($fila['objeto_slug'] ?? ''));
                        if ($slug !== '') {
                            $cant = max(1, (int) ($fila['cantidad'] ?? 1));
                            $etiqueta = ucwords(str_replace(array('-', '_'), ' ', $slug));
                            $items[] = array(
                                'nombre' => $etiqueta,
                                'slug' => $slug,
                                'cantidad' => $cant,
                            );
                        }
                    }
                }
                $stmt->close();
            }
        }

        if (empty($items)) {
            $stmt_c = $conexion->prepare("SELECT equipo_inicial FROM `clases` WHERE nombre = ? LIMIT 1");
            if ($stmt_c) {
                $stmt_c->bind_param('s', $clase_sel);
                if ($stmt_c->execute()) {
                    $res_c = $stmt_c->get_result();
                    if ($row_c = $res_c->fetch_assoc()) {
                        $crudo = trim((string) ($row_c['equipo_inicial'] ?? ''));
                        if ($crudo !== '') {
                            foreach (explode(',', $crudo) as $parte) {
                                $limpia = trim(rtrim($parte, '.'));
                                if ($limpia !== '') {
                                    $items[] = array(
                                        'nombre' => $limpia,
                                        'slug' => '',
                                        'cantidad' => 1,
                                    );
                                }
                            }
                        }
                    }
                }
                $stmt_c->close();
            }
        }
    } catch (mysqli_sql_exception $e) {
        return $items;
    }

    return $items;
}


function personaje_obtener_traits_entidad($tabla_intermedia, $columna_fk, $tabla_entidad, $columna_nombre, $valor_nombre, $origen_etiqueta)
{
    global $conexion;
    $traits = array();
    $valor_nombre = trim((string) $valor_nombre);

    if ($valor_nombre === '' || !isset($conexion) || !$conexion) {
        return $traits;
    }

    $t_int_esc = $conexion->real_escape_string($tabla_intermedia);
    $t_ent_esc = $conexion->real_escape_string($tabla_entidad);
    $c_fk_esc = $conexion->real_escape_string($columna_fk);

    try {
        $check_tbl = $conexion->query("SHOW TABLES LIKE '{$t_int_esc}'");
        $check_tr = $conexion->query("SHOW TABLES LIKE 'traits'");
        if (!$check_tbl || $check_tbl->num_rows === 0 || !$check_tr || $check_tr->num_rows === 0) {
            return $traits;
        }

        // Detectar columna de nombre en la entidad (`name` o `nombre`)
        $col_ent_nom = $conexion->real_escape_string($columna_nombre);
        $res_ent_c = $conexion->query("SHOW COLUMNS FROM `{$t_ent_esc}` LIKE '{$col_ent_nom}'");
        if (!$res_ent_c || $res_ent_c->num_rows === 0) {
            foreach (array('name', 'nombre') as $cand_ent) {
                $r_e = $conexion->query("SHOW COLUMNS FROM `{$t_ent_esc}` LIKE '{$cand_ent}'");
                if ($r_e && $r_e->num_rows > 0) {
                    $col_ent_nom = $cand_ent;
                    break;
                }
            }
        }

        // Detectar columna de nombre en `traits` (`nombre` o `name`)
        $col_nom = 'nombre';
        $res_n = $conexion->query("SHOW COLUMNS FROM `traits` LIKE 'nombre'");
        if (!$res_n || $res_n->num_rows === 0) {
            $res_n2 = $conexion->query("SHOW COLUMNS FROM `traits` LIKE 'name'");
            if ($res_n2 && $res_n2->num_rows > 0) {
                $col_nom = 'name';
            }
        }

        // Detectar columna de descripción en `traits` (`descripcion`, `description` o `desc`)
        $col_desc = null;
        foreach (array('descripcion', 'description', 'desc') as $cand_desc) {
            $res_d = $conexion->query("SHOW COLUMNS FROM `traits` LIKE '{$cand_desc}'");
            if ($res_d && $res_d->num_rows > 0) {
                $col_desc = $cand_desc;
                break;
            }
        }

        $sel_desc = $col_desc !== null ? ", t.`{$col_desc}` AS trait_desc" : ", '' AS trait_desc";

        $sql = "SELECT t.`{$col_nom}` AS trait_nombre{$sel_desc}
                FROM `{$t_int_esc}` ti
                INNER JOIN `traits` t ON ti.trait_id = t.id
                INNER JOIN `{$t_ent_esc}` e ON ti.`{$c_fk_esc}` = e.id
                WHERE LOWER(TRIM(e.`{$col_ent_nom}`)) = LOWER(TRIM(?))";

        $stmt = $conexion->prepare($sql);
        if ($stmt) {
            $stmt->bind_param('s', $valor_nombre);
            if ($stmt->execute()) {
                $res = $stmt->get_result();
                while ($fila = $res->fetch_assoc()) {
                    $nom = trim((string) ($fila['trait_nombre'] ?? ''));
                    if ($nom !== '') {
                        $traits[] = array(
                            'nombre' => $nom,
                            'descripcion' => trim((string) ($fila['trait_desc'] ?? '')),
                            'origen' => $origen_etiqueta,
                        );
                    }
                }
            }
            $stmt->close();
        }
    } catch (mysqli_sql_exception $e) {
        return $traits;
    }

    return $traits;
}

/**
 * Obtiene hechizos (spells) asociados a la clase o subclase seleccionada.
 */

function personaje_obtener_spells_clase_subclase($clase_sel, $subclase_sel = '')
{
    global $conexion;
    $spells = array();
    $clase_sel = trim((string) $clase_sel);
    $subclase_sel = trim((string) $subclase_sel);

    if (($clase_sel === '' && $subclase_sel === '') || !isset($conexion) || !$conexion) {
        return $spells;
    }

    $vistos = array();
    $agregar_spell = function ($nombre, $nivel = null, $origen = '') use (&$spells, &$vistos) {
        $nombre = trim((string) $nombre);
        if ($nombre === '') {
            return;
        }
        $niv_norm = ($nivel !== null && $nivel !== '') ? (int) $nivel : null;
        $clave = mb_strtolower($nombre, 'UTF-8') . '|' . ($niv_norm === null ? 'null' : (string) $niv_norm);
        if (isset($vistos[$clave])) {
            return;
        }
        $vistos[$clave] = true;
        $spells[] = array(
            'nombre' => $nombre,
            'nivel' => $niv_norm,
            'origen' => $origen,
        );
    };

    try {
        // Detecta si la tabla se llama `hechizos` o `spells`
        $tabla_spell = null;
        foreach (array('hechizos', 'spells') as $cand_sp) {
            $res_sp = $conexion->query("SHOW TABLES LIKE '{$cand_sp}'");
            if ($res_sp && $res_sp->num_rows > 0) {
                $tabla_spell = $cand_sp;
                break;
            }
        }

        if ($tabla_spell !== null) {
            $col_nom = 'nombre';
            $r_n = $conexion->query("SHOW COLUMNS FROM `{$tabla_spell}` LIKE 'nombre'");
            if (!$r_n || $r_n->num_rows === 0) {
                $r_n2 = $conexion->query("SHOW COLUMNS FROM `{$tabla_spell}` LIKE 'name'");
                if ($r_n2 && $r_n2->num_rows > 0) {
                    $col_nom = 'name';
                }
            }

            $col_niv = null;
            foreach (array('nivel', 'level') as $cand_niv) {
                $r_niv = $conexion->query("SHOW COLUMNS FROM `{$tabla_spell}` LIKE '{$cand_niv}'");
                if ($r_niv && $r_niv->num_rows > 0) {
                    $col_niv = $cand_niv;
                    break;
                }
            }

            // Tablas intermedias posibles para clase / subclase
            $intermedias = array(
                array('tabla' => 'clase_hechizo', 'fk' => 'clase_id', 'ent' => 'clases', 'col' => 'nombre', 'val' => $clase_sel, 'orig' => $clase_sel),
                array('tabla' => 'clase_hechizos', 'fk' => 'clase_id', 'ent' => 'clases', 'col' => 'nombre', 'val' => $clase_sel, 'orig' => $clase_sel),
                array('tabla' => 'clase_spell', 'fk' => 'clase_id', 'ent' => 'clases', 'col' => 'nombre', 'val' => $clase_sel, 'orig' => $clase_sel),
                array('tabla' => 'clase_spells', 'fk' => 'clase_id', 'ent' => 'clases', 'col' => 'nombre', 'val' => $clase_sel, 'orig' => $clase_sel),
                array('tabla' => 'subclase_hechizo', 'fk' => 'subclase_id', 'ent' => 'subclases', 'col' => 'nombre', 'val' => $subclase_sel, 'orig' => $subclase_sel),
                array('tabla' => 'subclase_hechizos', 'fk' => 'subclase_id', 'ent' => 'subclases', 'col' => 'nombre', 'val' => $subclase_sel, 'orig' => $subclase_sel),
                array('tabla' => 'subclase_spell', 'fk' => 'subclase_id', 'ent' => 'subclases', 'col' => 'nombre', 'val' => $subclase_sel, 'orig' => $subclase_sel),
                array('tabla' => 'subclase_spells', 'fk' => 'subclase_id', 'ent' => 'subclases', 'col' => 'nombre', 'val' => $subclase_sel, 'orig' => $subclase_sel),
            );

            $encontro_intermedia = false;
            foreach ($intermedias as $cfg) {
                if ($cfg['val'] === '') {
                    continue;
                }
                $t_int_e = $conexion->real_escape_string($cfg['tabla']);
                $r_int = $conexion->query("SHOW TABLES LIKE '{$t_int_e}'");
                if (!$r_int || $r_int->num_rows === 0) {
                    continue;
                }

                $fk_sp = 'hechizo_id';
                foreach (array('hechizo_id', 'spell_id') as $cand_fk_sp) {
                    $r_fk = $conexion->query("SHOW COLUMNS FROM `{$t_int_e}` LIKE '{$cand_fk_sp}'");
                    if ($r_fk && $r_fk->num_rows > 0) {
                        $fk_sp = $cand_fk_sp;
                        break;
                    }
                }

                $sel_niv = $col_niv !== null ? ", s.`{$col_niv}` AS spell_nivel" : "";
                $sql = "SELECT s.`{$col_nom}` AS spell_nombre{$sel_niv}
                        FROM `{$t_int_e}` ti
                        INNER JOIN `{$tabla_spell}` s ON ti.`{$fk_sp}` = s.id
                        INNER JOIN `{$cfg['ent']}` e ON ti.`{$cfg['fk']}` = e.id
                        WHERE e.`{$cfg['col']}` = ?";

                $stmt = $conexion->prepare($sql);
                if ($stmt) {
                    $stmt->bind_param('s', $cfg['val']);
                    if ($stmt->execute()) {
                        $res = $stmt->get_result();
                        while ($fila = $res->fetch_assoc()) {
                            $encontro_intermedia = true;
                            $niv = (isset($fila['spell_nivel']) && $fila['spell_nivel'] !== null) ? (int) $fila['spell_nivel'] : null;
                            $agregar_spell($fila['spell_nombre'] ?? '', $niv, $cfg['orig']);
                        }
                    }
                    $stmt->close();
                }
            }

            // Respaldo si todavía no hay tabla intermedia de hechizos pero existe `clases_permitidas`
            if (!$encontro_intermedia && $clase_sel !== '') {
                $r_cp = $conexion->query("SHOW COLUMNS FROM `{$tabla_spell}` LIKE 'clases_permitidas'");
                if ($r_cp && $r_cp->num_rows > 0) {
                    $sel_niv = $col_niv !== null ? ", `{$col_niv}` AS spell_nivel" : "";
                    $sql_fb = "SELECT `{$col_nom}` AS spell_nombre, clases_permitidas{$sel_niv}
                               FROM `{$tabla_spell}`";
                    $res_fb = $conexion->query($sql_fb);
                    if ($res_fb) {
                        $clase_lower = mb_strtolower($clase_sel, 'UTF-8');
                        while ($fila = $res_fb->fetch_assoc()) {
                            $permitidas = mb_strtolower((string) ($fila['clases_permitidas'] ?? ''), 'UTF-8');
                            if ($permitidas !== '' && mb_strpos($permitidas, $clase_lower) !== false) {
                                $niv = (isset($fila['spell_nivel']) && $fila['spell_nivel'] !== null) ? (int) $fila['spell_nivel'] : null;
                                $agregar_spell($fila['spell_nombre'] ?? '', $niv, $clase_sel);
                            }
                        }
                    }
                }
            }
        }
    } catch (mysqli_sql_exception $e) {
        return $spells;
    }

    // Ordenar todos los conjuros (clase + subclase) por nivel ascendente y luego por nombre
    usort($spells, function ($a, $b) {
        $niv_a = $a['nivel'] === null ? PHP_INT_MAX : (int) $a['nivel'];
        $niv_b = $b['nivel'] === null ? PHP_INT_MAX : (int) $b['nivel'];
        if ($niv_a !== $niv_b) {
            return $niv_a <=> $niv_b;
        }
        return strcmp(
            mb_strtolower((string) ($a['nombre'] ?? ''), 'UTF-8'),
            mb_strtolower((string) ($b['nombre'] ?? ''), 'UTF-8')
        );
    });

    return $spells;
}


function personaje_obtener_todos_objetos_equipo()
{
    global $conexion;
    $objetos = array();

    if (!isset($conexion) || !$conexion) {
        return $objetos;
    }

    try {
        $check_tbl = $conexion->query("SHOW TABLES LIKE 'objetos_equipo'");
        if (!$check_tbl || $check_tbl->num_rows === 0) {
            return $objetos;
        }

        $col_nom = 'nombre';
        $res_n = $conexion->query("SHOW COLUMNS FROM `objetos_equipo` LIKE 'nombre'");
        if (!$res_n || $res_n->num_rows === 0) {
            $res_n2 = $conexion->query("SHOW COLUMNS FROM `objetos_equipo` LIKE 'name'");
            if ($res_n2 && $res_n2->num_rows > 0) {
                $col_nom = 'name';
            }
        }

        $sql = "SELECT slug, `{$col_nom}` AS objeto_nombre FROM `objetos_equipo` ORDER BY `{$col_nom}` ASC, slug ASC";
        $res = $conexion->query($sql);
        if ($res) {
            while ($fila = $res->fetch_assoc()) {
                $slug = trim((string) ($fila['slug'] ?? ''));
                if ($slug === '') {
                    continue;
                }
                $nombre_raw = trim((string) ($fila['objeto_nombre'] ?? ''));
                $nombre = $nombre_raw !== '' ? $nombre_raw : ucwords(str_replace(array('-', '_'), ' ', $slug));
                $objetos[$slug] = $nombre;
            }
        }
    } catch (mysqli_sql_exception $e) {
        return $objetos;
    }

    return $objetos;
}