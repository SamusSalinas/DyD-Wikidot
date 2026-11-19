<?php
// Incluimos la conexión a tu base de datos 'dnd_wikidot'
require_once 'includes/conexion.php';

// Aumentamos el tiempo límite por seguridad
set_time_limit(300);

echo "<h2>Iniciando la importación de Trasfondos desde la API (2024)...</h2>";

// Array con los 4 trasfondos provistos
$backgrounds_data = [
    ["index" => "acolyte", "name" => "Acolyte", "url" => "/api/2024/backgrounds/acolyte"],
    ["index" => "criminal", "name" => "Criminal", "url" => "/api/2024/backgrounds/criminal"],
    ["index" => "sage", "name" => "Sage", "url" => "/api/2024/backgrounds/sage"],
    ["index" => "soldier", "name" => "Soldier", "url" => "/api/2024/backgrounds/soldier"]
];

$valores_trasfondos_sql = [];
$valores_bonos_sql = [];
$valores_prof_sql = [];
$importados = 0;

foreach ($backgrounds_data as $bg_meta) {
    $bg_url = "https://www.dnd5eapi.co" . $bg_meta['url'];
    
    $json_detalle = @file_get_contents($bg_url);
    if (!$json_detalle) {
        echo "<small style='color:orange;'>[Advertencia] No se pudo leer el detalle para: {$bg_meta['name']}</small><br>";
        continue;
    }
    
    $bg = json_decode($json_detalle, true);

    $index_api = $bg['index'];
    $name = $bg['name'];
    $feat_name = isset($bg['feat']['name']) ? $bg['feat']['name'] : '';
    
    // Tomamos la descripción de equipment_options si existe
    $equipment_desc = "";
    if (isset($bg['equipment_options'][0]['desc'])) {
        $equipment_desc = $bg['equipment_options'][0]['desc'];
    }

    // 1. Insertar o actualizar el trasfondo principal en la BD local
    $stmt_insert = $conexion->prepare("INSERT INTO trasfondos (index_api, name, feat_name, equipment_desc) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE name=VALUES(name), feat_name=VALUES(feat_name), equipment_desc=VALUES(equipment_desc)");
    $stmt_insert->bind_param("ssss", $index_api, $name, $feat_name, $equipment_desc);
    $stmt_insert->execute();
    
    // Obtenemos el ID del trasfondo
    $trasfondo_id = $stmt_insert->insert_id;
    if ($trasfondo_id == 0) {
        $stmt_id = $conexion->prepare("SELECT id FROM trasfondos WHERE index_api = ?");
        $stmt_id->bind_param("s", $index_api);
        $stmt_id->execute();
        $res_id = $stmt_id->get_result();
        if ($row_id = $res_id->fetch_assoc()) {
            $trasfondo_id = $row_id['id'];
        }
        $stmt_id->close();
    }
    $stmt_insert->close();

    // 2. Procesar Bonos de Características (ability_scores)
    if (isset($bg['ability_scores']) && is_array($bg['ability_scores'])) {
        foreach ($bg['ability_scores'] as $ability_item) {
            $ability_index = $ability_item['index']; // Ej: 'dex', 'con', 'int'

            $stmt_ab = $conexion->prepare("SELECT id FROM ability_scores WHERE index_api = ?");
            $stmt_ab->bind_param("s", $ability_index);
            $stmt_ab->execute();
            $res_ab = $stmt_ab->get_result();
            
            if ($row_ab = $res_ab->fetch_assoc()) {
                $ability_score_id = $row_ab['id'];

                $stmt_tb = $conexion->prepare("INSERT INTO trasfondo_bonus (trasfondo_id, ability_score_id) VALUES (?, ?) ON DUPLICATE KEY UPDATE trasfondo_id = trasfondo_id");
                $stmt_tb->bind_param("ii", $trasfondo_id, $ability_score_id);
                $stmt_tb->execute();
                $stmt_tb->close();

                $valores_bonos_sql[] = "((SELECT id FROM trasfondos WHERE index_api = '{$index_api}'), (SELECT id FROM ability_scores WHERE index_api = '{$ability_index}'))";
            }
            $stmt_ab->close();
        }
    }

    // 3. Procesar Proficiencias (proficiencies) vinculándolas con la tabla proficiencias
    if (isset($bg['proficiencies']) && is_array($bg['proficiencies'])) {
        foreach ($bg['proficiencies'] as $prof_item) {
            $prof_name = $prof_item['name']; // Ej: 'Skill: Stealth', 'Tool: Thieves\' Tools'

            $stmt_pr = $conexion->prepare("SELECT id FROM proficiencias WHERE nombre = ?");
            $stmt_pr->bind_param("s", $prof_name);
            $stmt_pr->execute();
            $res_pr = $stmt_pr->get_result();

            if ($row_pr = $res_pr->fetch_assoc()) {
                $proficiencia_id = $row_pr['id'];

                $stmt_tp = $conexion->prepare("INSERT INTO trasfondo_proficiencias (trasfondo_id, proficiencia_id) VALUES (?, ?) ON DUPLICATE KEY UPDATE trasfondo_id = trasfondo_id");
                $stmt_tp->bind_param("ii", $trasfondo_id, $proficiencia_id);
                $stmt_tp->execute();
                $stmt_tp->close();

                $prof_name_sql = $conexion->real_escape_string($prof_name);
                $valores_prof_sql[] = "((SELECT id FROM trasfondos WHERE index_api = '{$index_api}'), (SELECT id FROM proficiencias WHERE nombre = '{$prof_name_sql}'))";
            } else {
                echo "<small style='color:orange;'>[Aviso] La proficiencia '{$prof_name}' no se encontró en la tabla proficiencias.</small><br>";
            }
            $stmt_pr->close();
        }
    }

    // Acumular datos para el SQL de respaldo
    $index_sql = $conexion->real_escape_string($index_api);
    $name_sql = $conexion->real_escape_string($name);
    $feat_sql = $conexion->real_escape_string($feat_name);
    $eq_sql = $conexion->real_escape_string($equipment_desc);

    $valores_trasfondos_sql[] = "('{$index_sql}', '{$name_sql}', '{$feat_sql}', '{$eq_sql}')";

    $importados++;
    echo "<small style='color:green;'>[Procesado] Trasfondo: {$name}</small><br>";
}

// 4. Generar y guardar el archivo cargarTrasfondos.sql en la carpeta /database
$directorio_db = __DIR__ . '/database';
if (!is_dir($directorio_db)) {
    mkdir($directorio_db, 0777, true);
}

$ruta_archivo = $directorio_db . '/cargarTrasfondos.sql';

$contenido_sql = "-- Archivo de respaldo y carga masiva de Trasfondos (D&D 5e - 2024)\n"
               . "USE dyd_wikidot;\n\n"
               . "-- 1. Inserción de Trasfondos\n"
               . "INSERT INTO trasfondos (index_api, name, feat_name, equipment_desc) VALUES\n"
               . implode(",\n", $valores_trasfondos_sql)
               . "\nON DUPLICATE KEY UPDATE name=VALUES(name), feat_name=VALUES(feat_name), equipment_desc=VALUES(equipment_desc);\n\n";

if (!empty($valores_bonos_sql)) {
    $contenido_sql .= "-- 2. Inserción de Bonos de Características para Trasfondos\n"
                   . "INSERT INTO trasfondo_bonus (trasfondo_id, ability_score_id) VALUES\n"
                   . implode(",\n", $valores_bonos_sql)
                   . "\nON DUPLICATE KEY UPDATE trasfondo_id=trasfondo_id;\n\n";
}

if (!empty($valores_prof_sql)) {
    $contenido_sql .= "-- 3. Inserción de Proficiencias para Trasfondos\n"
                   . "INSERT INTO trasfondo_proficiencias (trasfondo_id, proficiencia_id) VALUES\n"
                   . implode(",\n", $valores_prof_sql)
                   . "\nON DUPLICATE KEY UPDATE trasfondo_id=trasfondo_id;\n";
}

file_put_contents($ruta_archivo, $contenido_sql);

echo "<hr>";
echo "<h3 style='color:blue;'>¡Proceso de trasfondos completado con éxito!</h3>";
echo "<p>Se procesaron e importaron <strong>{$importados}</strong> trasfondos a la base de datos.</p>";
echo "<p>Archivo SQL de respaldo guardado en: <strong>/database/cargarTrasfondos.sql</strong></p>";
?>