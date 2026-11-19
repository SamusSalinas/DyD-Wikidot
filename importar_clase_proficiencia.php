<?php
require_once __DIR__ . '/includes/conexion.php';

set_time_limit(300);

/**
 * Helper para hacer peticiones GET JSON usando cURL o file_get_contents.
 */
function api_get_json($url)
{
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_USERAGENT      => 'WikiDot-DnD5e-Importer/1.0',
        ));
        $raw = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($raw === false || $code >= 400) {
            return null;
        }
        return json_decode($raw, true);
    }

    $raw = @file_get_contents($url);
    return $raw ? json_decode($raw, true) : null;
}

/**
 * Mapea el 'index' en inglés de dnd5eapi con el nombre en español de la tabla `clases`.
 */
function resolver_clase_id_local(mysqli $conexion, $index_api, $nombre_api)
{
    $mapa_slugs = array(
        'barbarian' => 'Bárbaro',
        'bard'      => 'Bardo',
        'cleric'    => 'Clérigo',
        'druid'     => 'Druida',
        'fighter'   => 'Guerrero',
        'monk'      => 'Monje',
        'paladin'   => 'Paladín',
        'ranger'    => 'Explorador (Ranger)',
        'rogue'     => 'Pícaro',
        'sorcerer'  => 'Hechicero',
        'warlock'   => 'Brujo',
        'wizard'    => 'Mago',
    );

    $candidatos = array();
    $index_limpio = strtolower(trim((string) $index_api));

    if (isset($mapa_slugs[$index_limpio])) {
        $candidatos[] = $mapa_slugs[$index_limpio];
    }
    if (!empty($nombre_api)) {
        $candidatos[] = trim((string) $nombre_api);
    }

    foreach ($candidatos as $nombre_buscado) {
        $stmt = $conexion->prepare('SELECT id FROM clases WHERE LOWER(nombre) = LOWER(?) LIMIT 1');
        if ($stmt) {
            $stmt->bind_param('s', $nombre_buscado);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($fila = $res->fetch_assoc()) {
                $id = (int) $fila['id'];
                $stmt->close();
                return $id;
            }
            $stmt->close();
        }
    }

    return null;
}

/**
 * Obtiene el proficiencia_id en `proficiencias` por nombre (o lo crea si falta).
 */
function obtener_o_crear_proficiencia_id(mysqli $conexion, $nombre_prof)
{
    $nombre_prof = trim((string) $nombre_prof);
    if ($nombre_prof === '') {
        return null;
    }

    $stmt = $conexion->prepare('SELECT id FROM proficiencias WHERE nombre = ? LIMIT 1');
    if ($stmt) {
        $stmt->bind_param('s', $nombre_prof);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($fila = $res->fetch_assoc()) {
            $id = (int) $fila['id'];
            $stmt->close();
            return $id;
        }
        $stmt->close();
    }

    // Si no estaba cargada en `proficiencias`, la insertamos para conservar la relación
    $stmt_ins = $conexion->prepare('INSERT INTO proficiencias (nombre) VALUES (?)');
    if ($stmt_ins) {
        $stmt_ins->bind_param('s', $nombre_prof);
        if ($stmt_ins->execute()) {
            $nuevo_id = (int) $conexion->insert_id;
            $stmt_ins->close();
            return $nuevo_id;
        }
        $stmt_ins->close();
    }

    return null;
}

echo "<h2>Importando proficiencias por clase desde dnd5eapi (2014)...</h2>";

$url_listado = 'https://www.dnd5eapi.co/api/2014/classes';
$listado = api_get_json($url_listado);

if (empty($listado['results']) || !is_array($listado['results'])) {
    die("<p style='color:red;'>No se pudo obtener el listado de clases desde {$url_listado}.</p>");
}

$stmt_rel = $conexion->prepare(
    'INSERT IGNORE INTO clase_proficiencia (clase_id, proficiencia_id) VALUES (?, ?)'
);
if (!$stmt_rel) {
    die("<p style='color:red;'>Error preparando INSERT en clase_proficiencia: " . htmlspecialchars($conexion->error) . "</p>");
}

$total_relaciones = 0;

foreach ($listado['results'] as $item_clase) {
    $index_clase = $item_clase['index'] ?? '';
    $nombre_clase_api = $item_clase['name'] ?? '';
    $url_detalle = !empty($item_clase['url'])
        ? 'https://www.dnd5eapi.co' . $item_clase['url']
        : "https://www.dnd5eapi.co/api/2014/classes/{$index_clase}";

    $clase_id = resolver_clase_id_local($conexion, $index_clase, $nombre_clase_api);
    if (!$clase_id) {
        echo "<p style='color:orange;'>Clase no encontrada en BD local: <strong>" . htmlspecialchars($nombre_clase_api) . "</strong> ({$index_clase}) — omitida.</p>";
        continue;
    }

    $detalle = api_get_json($url_detalle);
    if (empty($detalle)) {
        echo "<p style='color:red;'>Error al consultar detalle de {$nombre_clase_api}.</p>";
        continue;
    }

    $proficiencias = $detalle['proficiencies'] ?? array();
    $insertadas_clase = 0;

    foreach ($proficiencias as $prof) {
        $nombre_prof = trim((string) ($prof['name'] ?? ''));
        if ($nombre_prof === '') {
            continue;
        }

        $prof_id = obtener_o_crear_proficiencia_id($conexion, $nombre_prof);
        if ($prof_id) {
            $stmt_rel->bind_param('ii', $clase_id, $prof_id);
            $stmt_rel->execute();
            if ($stmt_rel->affected_rows > 0) {
                $insertadas_clase++;
                $total_relaciones++;
            }
        }
    }

    echo "<p>Clase <strong>" . htmlspecialchars($nombre_clase_api) . "</strong> (ID {$clase_id}): "
        . count($proficiencias) . " proficiencias en API, <strong>{$insertadas_clase}</strong> nuevas relaciones guardadas.</p>";
}

$stmt_rel->close();
echo "<h3 style='color:green;'>¡Listo! Total de nuevas relaciones en clase_proficiencia: {$total_relaciones}.</h3>";