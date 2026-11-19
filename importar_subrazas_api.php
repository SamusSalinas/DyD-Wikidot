<?php
// Incluimos la conexión a tu base de datos 'dnd_wikidot'
require_once 'includes/conexion.php';

// Aumentamos el tiempo límite por si la API tarda en responder
set_time_limit(300);

echo "<h2>Iniciando la importación de Subrazas desde la API...</h2>";

$url_api_subraces = "https://www.dnd5eapi.co/api/2014/subraces/";
$json_lista = @file_get_contents($url_api_subraces);

if (!$json_lista) {
    die("<p style='color:red;'>Error al conectar con la API de D&D 5e en el listado de subrazas.</p>");
}

$data_lista = json_decode($json_lista, true);

if (!isset($data_lista['results'])) {
    die("<p style='color:red;'>Error: La respuesta de la API no contiene resultados válidos para subrazas.</p>");
}

$subraces = $data_lista['results'];
echo "<p>Se encontraron " . count($subraces) . " subrazas en la lista principal. Procesando...</p>";

$valores_subrazas_sql = [];
$valores_bonos_sql = [];
$valores_traits_sql = [];
$importadas = 0;

foreach ($subraces as $subrace_meta) {
    $subrace_url = "https://www.dnd5eapi.co" . $subrace_meta['url'];
    
    $json_detalle = @file_get_contents($subrace_url);
    if (!$json_detalle) {
        echo "<small style='color:orange;'>[Advertencia] No se pudo leer el detalle para: {$subrace_meta['name']}</small><br>";
        continue;
    }
    
    $subrace = json_decode($json_detalle, true);

    $name = $subrace['name'];
    $speed = isset($subrace['speed']) ? $subrace['speed'] : null;
    $age = isset($subrace['age']) ? $subrace['age'] : '';
    $alignment = isset($subrace['alignment']) ? $subrace['alignment'] : '';
    $size = isset($subrace['size']) ? $subrace['size'] : '';
    $size_description = isset($subrace['size_description']) ? $subrace['size_description'] : '';
    $language_desc = isset($subrace['language_desc']) ? $subrace['language_desc'] : '';

    // 1. Obtener la raza principal (padre) a la que pertenece esta subraza
    $raza_id = null;
    $raza_nombre_padre = "";
    if (isset($subrace['race']['name'])) {
        $raza_nombre_padre = $subrace['race']['name'];
        
        $stmt_r = $conexion->prepare("SELECT id FROM razas WHERE name = ?");
        $stmt_r->bind_param("s", $raza_nombre_padre);
        $stmt_r->execute();
        $res_r = $stmt_r->get_result();
        if ($row_r = $res_r->fetch_assoc()) {
            $raza_id = $row_r['id'];
        }
        $stmt_r->close();
    }

    if (!$raza_id) {
        echo "<small style='color:red;'>[Error] No se encontró la raza padre para la subraza: {$name}</small><br>";
        continue;
    }

    // 2. Idiomas: unimos los nombres separados por coma
    $languages_array = [];
    if (isset($subrace['languages']) && is_array($subrace['languages'])) {
        foreach ($subrace['languages'] as $lang) {
            $languages_array[] = $lang['name'];
        }
    }
    $languages_str = implode(', ', $languages_array);

    // 3. Insertar o actualizar la subraza en la BD local
    $stmt_insert = $conexion->prepare("INSERT INTO subrazas (raza_id, name, speed, age, alignment, size, size_description, languages, language_desc) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE speed=VALUES(speed), age=VALUES(age), alignment=VALUES(alignment), size=VALUES(size), size_description=VALUES(size_description), languages=VALUES(languages), language_desc=VALUES(language_desc)");
    $stmt_insert->bind_param("isissssss", $raza_id, $name, $speed, $age, $alignment, $size, $size_description, $languages_str, $language_desc);
    $stmt_insert->execute();
    
    // Obtenemos el ID de la subraza
    $subraza_id = $stmt_insert->insert_id;
    if ($subraza_id == 0) {
        $stmt_id = $conexion->prepare("SELECT id FROM subrazas WHERE name = ?");
        $stmt_id->bind_param("s", $name);
        $stmt_id->execute();
        $res_id = $stmt_id->get_result();
        if ($row_id = $res_id->fetch_assoc()) {
            $subraza_id = $row_id['id'];
        }
        $stmt_id->close();
    }
    $stmt_insert->close();

    // 4. Procesar Bonos de Características (ability_bonuses)
    if (isset($subrace['ability_bonuses']) && is_array($subrace['ability_bonuses'])) {
        foreach ($subrace['ability_bonuses'] as $bonus_item) {
            $ability_index = $bonus_item['ability_score']['index']; 
            $bonus_value = $bonus_item['bonus'];

            $stmt_ab = $conexion->prepare("SELECT id FROM ability_scores WHERE index_api = ?");
            $stmt_ab->bind_param("s", $ability_index);
            $stmt_ab->execute();
            $res_ab = $stmt_ab->get_result();
            
            if ($row_ab = $res_ab->fetch_assoc()) {
                $ability_score_id = $row_ab['id'];

                $stmt_rb = $conexion->prepare("INSERT INTO subraza_bonus (subraza_id, ability_score_id, valor) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE valor = VALUES(valor)");
                $stmt_rb->bind_param("iii", $subraza_id, $ability_score_id, $bonus_value);
                $stmt_rb->execute();
                $stmt_rb->close();

                $valores_bonos_sql[] = "((SELECT id FROM subrazas WHERE name = '{$conexion->real_escape_string($name)}'), (SELECT id FROM ability_scores WHERE index_api = '{$ability_index}'), {$bonus_value})";
            }
            $stmt_ab->close();
        }
    }

    // 5. Procesar Traits de la Subraza (En subrazas la API usa 'racial_traits')
    $traits_array = isset($subrace['racial_traits']) ? $subrace['racial_traits'] : [];
    
    if (!empty($traits_array) && is_array($traits_array)) {
        foreach ($traits_array as $trait_item) {
            $trait_index = $trait_item['index'];

            $stmt_tr = $conexion->prepare("SELECT id FROM traits WHERE index_api = ?");
            $stmt_tr->bind_param("s", $trait_index);
            $stmt_tr->execute();
            $res_tr = $stmt_tr->get_result();

            if ($row_tr = $res_tr->fetch_assoc()) {
                $trait_id = $row_tr['id'];

                $stmt_rt = $conexion->prepare("INSERT INTO subraza_trait (subraza_id, trait_id) VALUES (?, ?) ON DUPLICATE KEY UPDATE subraza_id = subraza_id");
                $stmt_rt->bind_param("ii", $subraza_id, $trait_id);
                $stmt_rt->execute();
                $stmt_rt->close();

                $valores_traits_sql[] = "((SELECT id FROM subrazas WHERE name = '{$conexion->real_escape_string($name)}'), (SELECT id FROM traits WHERE index_api = '{$trait_index}'))";
            }
            $stmt_tr->close();
        }
    }

    // Acumular datos para el SQL de respaldo
    $name_sql = $conexion->real_escape_string($name);
    $speed_sql = ($speed !== null) ? $speed : "NULL";
    $age_sql = $conexion->real_escape_string($age);
    $alignment_sql = $conexion->real_escape_string($alignment);
    $size_sql = $conexion->real_escape_string($size);
    $size_desc_sql = $conexion->real_escape_string($size_description);
    $languages_sql = $conexion->real_escape_string($languages_str);
    $language_desc_sql = $conexion->real_escape_string($language_desc);
    $raza_padre_sql = $conexion->real_escape_string($raza_nombre_padre);

    $valores_subrazas_sql[] = "((SELECT id FROM razas WHERE name = '{$raza_padre_sql}'), '{$name_sql}', {$speed_sql}, '{$age_sql}', '{$alignment_sql}', '{$size_sql}', '{$size_desc_sql}', '{$languages_sql}', '{$language_desc_sql}')";

    $importadas++;
    echo "<small style='color:green;'>[Procesada] Subraza: {$name}</small><br>";
}

// 6. Generar y guardar el archivo cargarSubrazas.sql en la carpeta /database
$directorio_db = __DIR__ . '/database';
if (!is_dir($directorio_db)) {
    mkdir($directorio_db, 0777, true);
}

$ruta_archivo = $directorio_db . '/cargarSubrazas.sql';

$contenido_sql = "-- Archivo de respaldo y carga masiva de Subrazas (D&D 5e)\n"
               . "USE dyd_wikidot;\n\n"
               . "-- 1. Inserción de Subrazas\n"
               . "INSERT INTO subrazas (raza_id, name, speed, age, alignment, size, size_description, languages, language_desc) VALUES\n"
               . implode(",\n", $valores_subrazas_sql)
               . "\nON DUPLICATE KEY UPDATE speed=VALUES(speed), age=VALUES(age), alignment=VALUES(alignment), size=VALUES(size), size_description=VALUES(size_description), languages=VALUES(languages), language_desc=VALUES(language_desc);\n\n";

if (!empty($valores_bonos_sql)) {
    $contenido_sql .= "-- 2. Inserción de Bonos de Características para Subrazas\n"
                   . "INSERT INTO subraza_bonus (subraza_id, ability_score_id, valor) VALUES\n"
                   . implode(",\n", $valores_bonos_sql)
                   . "\nON DUPLICATE KEY UPDATE valor=VALUES(valor);\n\n";
}

if (!empty($valores_traits_sql)) {
    $contenido_sql .= "-- 3. Inserción de Relaciones de Traits con Subrazas\n"
                   . "INSERT INTO subraza_trait (subraza_id, trait_id) VALUES\n"
                   . implode(",\n", $valores_traits_sql)
                   . "\nON DUPLICATE KEY UPDATE subraza_id=subraza_id;\n";
}

file_put_contents($ruta_archivo, $contenido_sql);

echo "<hr>";
echo "<h3 style='color:blue;'>¡Proceso de subrazas completado con éxito!</h3>";
echo "<p>Se procesaron e importaron <strong>{$importadas}</strong> subrazas a la base de datos.</p>";
echo "<p>Archivo SQL de respaldo guardado en: <strong>/database/cargarSubrazas.sql</strong></p>";
?>