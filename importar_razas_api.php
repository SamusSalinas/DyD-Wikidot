<?php
// Incluimos la conexión a tu base de datos 'dnd_wikidot'
require_once 'includes/conexion.php';

// Aumentamos el tiempo límite por si la API tarda en responder
set_time_limit(300);

echo "<h2>Iniciando la importación de Razas desde la API...</h2>";

$url_api_races = "https://www.dnd5eapi.co/api/2014/races/";
$json_lista = @file_get_contents($url_api_races);

if (!$json_lista) {
    die("<p style='color:red;'>Error al conectar con la API de D&D 5e en el listado de razas.</p>");
}

$data_lista = json_decode($json_lista, true);

if (!isset($data_lista['results'])) {
    die("<p style='color:red;'>Error: La respuesta de la API no contiene resultados válidos.</p>");
}

$races = $data_lista['results'];
echo "<p>Se encontraron " . count($races) . " razas en la lista principal. Procesando...</p>";

$valores_razas_sql = [];
$valores_bonos_sql = [];
$valores_traits_sql = [];
$importadas = 0;

foreach ($races as $race_meta) {
    // CORRECCIÓN: La URL que devuelve la API ya incluye '/api/...' completo
    $race_url = "https://www.dnd5eapi.co" . $race_meta['url'];
    
    $json_detalle = @file_get_contents($race_url);
    if (!$json_detalle) {
        echo "<small style='color:orange;'>[Advertencia] No se pudo leer el detalle para: {$race_meta['name']}</small><br>";
        continue;
    }
    
    $race = json_decode($json_detalle, true);

    $name = $race['name'];
    $speed = isset($race['speed']) ? $race['speed'] : 30;
    $age = isset($race['age']) ? $race['age'] : '';
    $alignment = isset($race['alignment']) ? $race['alignment'] : '';
    $size = isset($race['size']) ? $race['size'] : '';
    $size_description = isset($race['size_description']) ? $race['size_description'] : '';
    $language_desc = isset($race['language_desc']) ? $race['language_desc'] : '';

    // 1. Idiomas: unimos los nombres separados por coma
    $languages_array = [];
    if (isset($race['languages']) && is_array($race['languages'])) {
        foreach ($race['languages'] as $lang) {
            $languages_array[] = $lang['name'];
        }
    }
    $languages_str = implode(', ', $languages_array);

    // 2. Insertar o actualizar la raza principal en la BD local
    $stmt_insert = $conexion->prepare("INSERT INTO razas (name, speed, age, alignment, size, size_description, languages, language_desc) VALUES (?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE speed=VALUES(speed), age=VALUES(age), alignment=VALUES(alignment), size=VALUES(size), size_description=VALUES(size_description), languages=VALUES(languages), language_desc=VALUES(language_desc)");
    $stmt_insert->bind_param("sissssss", $name, $speed, $age, $alignment, $size, $size_description, $languages_str, $language_desc);
    $stmt_insert->execute();
    
    // Obtenemos el ID de la raza
    $raza_id = $stmt_insert->insert_id;
    if ($raza_id == 0) {
        $stmt_id = $conexion->prepare("SELECT id FROM razas WHERE name = ?");
        $stmt_id->bind_param("s", $name);
        $stmt_id->execute();
        $res_id = $stmt_id->get_result();
        if ($row_id = $res_id->fetch_assoc()) {
            $raza_id = $row_id['id'];
        }
        $stmt_id->close();
    }
    $stmt_insert->close();

    // 3. Procesar Bonos de Características (ability_bonuses)
    if (isset($race['ability_bonuses']) && is_array($race['ability_bonuses'])) {
        foreach ($race['ability_bonuses'] as $bonus_item) {
            $ability_index = $bonus_item['ability_score']['index']; // Ej: 'dex', 'str'
            $bonus_value = $bonus_item['bonus'];

            $stmt_ab = $conexion->prepare("SELECT id FROM ability_scores WHERE index_api = ?");
            $stmt_ab->bind_param("s", $ability_index);
            $stmt_ab->execute();
            $res_ab = $stmt_ab->get_result();
            
            if ($row_ab = $res_ab->fetch_assoc()) {
                $ability_score_id = $row_ab['id'];

                $stmt_rb = $conexion->prepare("INSERT INTO raza_bonus (raza_id, ability_score_id, valor) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE valor = VALUES(valor)");
                $stmt_rb->bind_param("iii", $raza_id, $ability_score_id, $bonus_value);
                $stmt_rb->execute();
                $stmt_rb->close();

                $valores_bonos_sql[] = "((SELECT id FROM razas WHERE name = '{$conexion->real_escape_string($name)}'), (SELECT id FROM ability_scores WHERE index_api = '{$ability_index}'), {$bonus_value})";
            }
            $stmt_ab->close();
        }
    }

    // 4. Procesar Traits de la Raza (traits)
    if (isset($race['traits']) && is_array($race['traits'])) {
        foreach ($race['traits'] as $trait_item) {
            $trait_index = $trait_item['index'];

            $stmt_tr = $conexion->prepare("SELECT id FROM traits WHERE index_api = ?");
            $stmt_tr->bind_param("s", $trait_index);
            $stmt_tr->execute();
            $res_tr = $stmt_tr->get_result();

            if ($row_tr = $res_tr->fetch_assoc()) {
                $trait_id = $row_tr['id'];

                $stmt_rt = $conexion->prepare("INSERT INTO raza_trait (raza_id, trait_id) VALUES (?, ?) ON DUPLICATE KEY UPDATE raza_id = raza_id");
                $stmt_rt->bind_param("ii", $raza_id, $trait_id);
                $stmt_rt->execute();
                $stmt_rt->close();

                $valores_traits_sql[] = "((SELECT id FROM razas WHERE name = '{$conexion->real_escape_string($name)}'), (SELECT id FROM traits WHERE index_api = '{$trait_index}'))";
            }
            $stmt_tr->close();
        }
    }

    // Acumular datos para el SQL de respaldo
    $name_sql = $conexion->real_escape_string($name);
    $age_sql = $conexion->real_escape_string($age);
    $alignment_sql = $conexion->real_escape_string($alignment);
    $size_sql = $conexion->real_escape_string($size);
    $size_desc_sql = $conexion->real_escape_string($size_description);
    $languages_sql = $conexion->real_escape_string($languages_str);
    $language_desc_sql = $conexion->real_escape_string($language_desc);

    $valores_razas_sql[] = "('{$name_sql}', {$speed}, '{$age_sql}', '{$alignment_sql}', '{$size_sql}', '{$size_desc_sql}', '{$languages_sql}', '{$language_desc_sql}')";

    $importadas++;
    echo "<small style='color:green;'>[Procesada] Raza: {$name}</small><br>";
}

// 5. Generar y guardar el archivo cargarRazas.sql en la carpeta /database
$directorio_db = __DIR__ . '/database';
if (!is_dir($directorio_db)) {
    mkdir($directorio_db, 0777, true);
}

$ruta_archivo = $directorio_db . '/cargarRazas.sql';

$contenido_sql = "-- Archivo de respaldo y carga masiva de Razas (D&D 5e)\n"
               . "USE dyd_wikidot;\n\n"
               . "-- 1. Inserción de Razas\n"
               . "INSERT INTO razas (name, speed, age, alignment, size, size_description, languages, language_desc) VALUES\n"
               . implode(",\n", $valores_razas_sql)
               . "\nON DUPLICATE KEY UPDATE speed=VALUES(speed), age=VALUES(age), alignment=VALUES(alignment), size=VALUES(size), size_description=VALUES(size_description), languages=VALUES(languages), language_desc=VALUES(language_desc);\n\n";

if (!empty($valores_bonos_sql)) {
    $contenido_sql .= "-- 2. Inserción de Bonos de Características\n"
                   . "INSERT INTO raza_bonus (raza_id, ability_score_id, valor) VALUES\n"
                   . implode(",\n", $valores_bonos_sql)
                   . "\nON DUPLICATE KEY UPDATE valor=VALUES(valor);\n\n";
}

if (!empty($valores_traits_sql)) {
    $contenido_sql .= "-- 3. Inserción de Relaciones con Traits\n"
                   . "INSERT INTO raza_trait (raza_id, trait_id) VALUES\n"
                   . implode(",\n", $valores_traits_sql)
                   . "\nON DUPLICATE KEY UPDATE raza_id=raza_id;\n";
}

file_put_contents($ruta_archivo, $contenido_sql);

echo "<hr>";
echo "<h3 style='color:blue;'>¡Proceso de razas completado con éxito!</h3>";
echo "<p>Se procesaron e importaron <strong>{$importadas}</strong> razas a la base de datos.</p>";
echo "<p>Archivo SQL de respaldo guardado en: <strong>/database/cargarRazas.sql</strong></p>";
?>