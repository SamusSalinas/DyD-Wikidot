<?php
require_once __DIR__ . '/conexion.php';

/**
 * Devuelve la lista de atributos principales de D&D 5e
 */
function personaje_atributos() {
    return [
        'fuerza'     => ['nombre' => 'Fuerza', 'campo' => 'at_fuerza'],
        'destreza'   => ['nombre' => 'Destreza', 'campo' => 'at_destreza'],
        'constitucion' => ['nombre' => 'Constitución', 'campo' => 'at_constitucion'],
        'inteligencia' => ['nombre' => 'Inteligencia', 'campo' => 'at_inteligencia'],
        'sabiduria'  => ['nombre' => 'Sabiduría', 'campo' => 'at_sabiduria'],
        'carisma'    => ['nombre' => 'Carisma', 'campo' => 'at_carisma']
    ];
}

/**
 * Devuelve las habilidades y su atributo asociado
 */
function personaje_habilidades() {
    return [
        'acrobacias'       => ['nombre' => 'Acrobacias', 'atributo' => 'destreza'],
        'arcanos'          => ['nombre' => 'Arcanos', 'atributo' => 'inteligencia'],
        'atletismo'        => ['nombre' => 'Atletismo', 'atributo' => 'fuerza'],
        'engaño'           => ['nombre' => 'Engaño', 'atributo' => 'carisma'],
        'historia'         => ['nombre' => 'Historia', 'atributo' => 'inteligencia'],
        'interpretacion'   => ['nombre' => 'Interpretación', 'atributo' => 'carisma'],
        'intimidacion'     => ['nombre' => 'Intimidación', 'atributo' => 'carisma'],
        'investigacion'    => ['nombre' => 'Investigación', 'atributo' => 'inteligencia'],
        'juego_de_manos'   => ['nombre' => 'Juego de Manos', 'atributo' => 'destreza'],
        'medicina'         => ['nombre' => 'Medicina', 'atributo' => 'sabiduria'],
        'naturaleza'       => ['nombre' => 'Naturaleza', 'atributo' => 'inteligencia'],
        'percepcion'       => ['nombre' => 'Percepción', 'atributo' => 'sabiduria'],
        'perspicacia'      => ['nombre' => 'Perspicacia', 'atributo' => 'sabiduria'],
        'persuasion'       => ['nombre' => 'Persuasión', 'atributo' => 'carisma'],
        'religion'         => ['nombre' => 'Religión', 'atributo' => 'inteligencia'],
        'sigilo'           => ['nombre' => 'Sigilo', 'atributo' => 'destreza'],
        'supervivencia'    => ['nombre' => 'Supervivencia', 'atributo' => 'sabiduria'],
        'trato_con_animales' => ['nombre' => 'Trato con Animales', 'atributo' => 'sabiduria']
    ];
}

/**
 * Obtiene las opciones de clases desde la base de datos local
 */
function personaje_opciones_clases() {
    global $conexion;
    $clases = [];
    $resultado = $conexion->query("SELECT nombre FROM clases ORDER BY nombre ASC");
    if ($resultado) {
        while ($row = $resultado->fetch_assoc()) {
            $clases[] = $row['nombre'];
        }
    }
    return $clases;
}

/**
 * Obtiene las opciones de razas desde la base de datos local
 */
function personaje_opciones_razas() {
    global $conexion;
    $razas = [];
    $resultado = $conexion->query("SELECT nombre FROM razas ORDER BY nombre ASC");
    if ($resultado) {
        while ($row = $resultado->fetch_assoc()) {
            $razas[] = $row['nombre'];
        }
    }
    return $razas;
}

/**
 * Calcula el modificador de un atributo de D&D (Puntuación - 10) / 2
 */
function personaje_modificador($puntuacion) {
    return floor(((int)$puntuacion - 10) / 2);
}

/**
 * Formatea el modificador con signo (+ o -)
 */
function personaje_formatear_modificador($modificador) {
    return ($modificador >= 0 ? '+' : '') . $modificador;
}

/**
 * Calcula el bono de competencia según el nivel del personaje
 */
function personaje_bono_competencia($nivel) {
    $nivel = max(1, min(20, (int)$nivel));
    return floor(($nivel - 1) / 4) + 2;
}

/**
 * Calcula el modificador de una habilidad específica
 */
function personaje_modificador_habilidad($clave_habilidad, $personaje) {
    $habilidades = personaje_habilidades();
    if (!isset($habilidades[$clave_habilidad])) return 0;
    
    $atributo_asociado = $habilidades[$clave_habilidad]['atributo'];
    $puntuacion = isset($personaje['atributos'][$sub = substr($atributo_asociado, 0, 3)]) ? $personaje['atributos'][substr($atributo_asociado, 0, 3)] : 10;
    
    // Mapeo corto de abreviaturas de atributos
    $mapa = ['fuerza' => 'fue', 'destreza' => 'dex', 'constitucion' => 'con', 'inteligencia' => 'int', 'sabiduria' => 'sab', 'carisma' => 'car'];
    $codigo_corto = isset($mapa[$atributo_asociado]) ? $mapa[$atributo_asociado] : 'fue';
    
    $puntuacion_attr = isset($personaje['atributos'][$codigo_corto]) ? $personaje['atributos'][$codigo_corto] : 10;
    $mod = personaje_modificador($puntuacion_attr);
    
    if (in_array($clave_habilidad, $personaje['habilidades'], true)) {
        $mod += personaje_bono_competencia($personaje['nivel']);
    }
    
    return $mod;
}

/**
 * Procesa y valida los datos enviados por POST para la hoja de personaje
 */
function personaje_generar_hoja($datos_post) {
    $errores = [];
    
    // Estructura por defecto del personaje
    $personaje = [
        'nombre' => isset($datos_post['nombre']) ? trim($datos_post['nombre']) : '',
        'jugador' => isset($datos_post['jugador']) ? trim($datos_post['jugador']) : '',
        'raza' => isset($datos_post['raza']) ? trim($datos_post['raza']) : '',
        'clase' => isset($datos_post['clase']) ? trim($datos_post['clase']) : '',
        'trasfondo' => isset($datos_post['trasfondo']) ? trim($datos_post['trasfondo']) : '',
        'alineamiento' => isset($datos_post['alineamiento']) ? trim($datos_post['alineamiento']) : '',
        'nivel' => isset($datos_post['nivel']) ? max(1, min(20, (int)$datos_post['nivel'])) : 1,
        'experiencia' => isset($datos_post['experiencia']) ? max(0, (int)$datos_post['experiencia']) : 0,
        
        'atributos' => [
            'fue' => isset($datos_post['at_fuerza']) ? max(1, min(30, (int)$datos_post['at_fuerza'])) : 10,
            'dex' => isset($datos_post['at_destreza']) ? max(1, min(30, (int)$datos_post['at_destreza'])) : 10,
            'con' => isset($datos_post['at_constitucion']) ? max(1, min(30, (int)$datos_post['at_constitucion'])) : 10,
            'int' => isset($datos_post['at_inteligencia']) ? max(1, min(30, (int)$datos_post['at_inteligencia'])) : 10,
            'sab' => isset($datos_post['at_sabiduria']) ? max(1, min(30, (int)$datos_post['at_sabiduria'])) : 10,
            'car' => isset($datos_post['at_carisma']) ? max(1, min(30, (int)$datos_post['at_carisma'])) : 10,
        ],
        
        'salvaciones' => isset($datos_post['salvaciones']) && is_array($datos_post['salvaciones']) ? $datos_post['salvaciones'] : [],
        'habilidades' => isset($datos_post['habilidades']) && is_array($datos_post['habilidades']) ? $datos_post['habilidades'] : [],
        
        'clase_armadura' => isset($datos_post['clase_armadura']) ? (int)$datos_post['clase_armadura'] : 10,
        'velocidad' => isset($datos_post['velocidad']) ? (int)$datos_post['velocidad'] : 9,
        'puntos_golpe_actuales' => isset($datos_post['puntos_golpe_actuales']) ? (int)$datos_post['puntos_golpe_actuales'] : 10,
        'puntos_golpe_maximos' => isset($datos_post['puntos_golpe_maximos']) ? (int)$datos_post['puntos_golpe_maximos'] : 10,
        'puntos_golpe_temporales' => isset($datos_post['puntos_golpe_temporales']) ? (int)$datos_post['puntos_golpe_temporales'] : 0,
        
        'equipo' => isset($datos_post['equipo']) ? trim($datos_post['equipo']) : '',
        'rasgos' => isset($datos_post['rasgos']) ? trim($datos_post['rasgos']) : ''
    ];

    // Validaciones básicas
    if (empty($personaje['nombre'])) {
        $errores[] = "El nombre del personaje es obligatorio.";
    }

    return [
        'datos' => $personaje,
        'errores' => $errores
    ];
}

/**
 * Obtiene las opciones de trasfondos desde la base de datos local
 */
function personaje_opciones_trasfondos() {
    global $conexion;
    $trasfondos = [];
    $resultado = $conexion->query("SELECT nombre FROM trasfondos ORDER BY nombre ASC");
    if ($resultado) {
        while ($row = $resultado->fetch_assoc()) {
            $trasfondos[] = $row['nombre'];
        }
    }
    return $trasfondos;
}

/**
 * Obtiene las subclases según la clase seleccionada
 */
function personaje_opciones_subclases($nombre_clase = '') {
    global $conexion;
    $subclases = [];
    if (empty($nombre_clase)) return $subclases;
    
    $stmt = $conexion->prepare("SELECT s.nombre FROM subclases s INNER JOIN clases c ON s.clase_id = c.id WHERE c.nombre = ? ORDER BY s.nombre ASC");
    $stmt->bind_param("s", $nombre_clase);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $subclases[] = $row['nombre'];
    }
    $stmt->close();
    return $subclases;
}