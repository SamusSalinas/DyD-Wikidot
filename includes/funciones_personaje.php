<?php
function personaje_opciones_clases()
{
    return array(
        'Barbarian', 'Bard', 'Cleric', 'Druid', 'Fighter', 'Monk',
        'Paladin', 'Ranger', 'Rogue', 'Sorcerer', 'Warlock', 'Wizard'
    );
}

function personaje_opciones_razas()
{
    return array(
        'Dragonborn', 'Dwarf', 'Elf', 'Gnome', 'Half-Elf', 'Half-Orc',
        'Halfling', 'Human', 'Tiefling'
    );
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

function personaje_generar_hoja($entrada)
{
    $errores = array();
    if (!is_array($entrada)) {
        $entrada = array();
        $errores[] = 'Los datos recibidos para la hoja no son válidos.';
    }

    $clases = personaje_opciones_clases();
    $razas = personaje_opciones_razas();
    $habilidades = personaje_habilidades();
    $atributos = personaje_atributos();
    $datos = array(
        'nombre' => personaje_valor_texto($entrada, 'nombre', 80, $errores),
        'jugador' => personaje_valor_texto($entrada, 'jugador', 80, $errores),
        'trasfondo' => personaje_valor_texto($entrada, 'trasfondo', 80, $errores),
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
        'equipo' => personaje_valor_texto($entrada, 'equipo', 2000, $errores),
        'rasgos' => personaje_valor_texto($entrada, 'rasgos', 2000, $errores)
    );

    if (isset($entrada['clase']) && in_array($entrada['clase'], $clases, true)) {
        $datos['clase'] = $entrada['clase'];
    } elseif (isset($entrada['clase']) && $entrada['clase'] !== '') {
        $errores[] = 'Selecciona una clase válida.';
    }

    if (isset($entrada['raza']) && in_array($entrada['raza'], $razas, true)) {
        $datos['raza'] = $entrada['raza'];
    } elseif (isset($entrada['raza']) && $entrada['raza'] !== '') {
        $errores[] = 'Selecciona una raza válida.';
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
