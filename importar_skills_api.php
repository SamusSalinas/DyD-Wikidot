<?php
// Incluimos la conexión a tu base de datos 'dnd_wikidot'
require_once 'includes/conexion.php';

// Aumentamos el tiempo límite por seguridad
set_time_limit(300);

echo "<h2>Iniciando la importación de Skills desde la API...</h2>";

// Array con las 18 skills provistas
$skills_data = [
    ["index" => "acrobatics", "name" => "Acrobatics", "url" => "/api/2014/skills/acrobatics"],
    ["index" => "animal-handling", "name" => "Animal Handling", "url" => "/api/2014/skills/animal-handling"],
    ["index" => "arcana", "name" => "Arcana", "url" => "/api/2014/skills/arcana"],
    ["index" => "athletics", "name" => "Athletics", "url" => "/api/2014/skills/athletics"],
    ["index" => "deception", "name" => "Deception", "url" => "/api/2014/skills/deception"],
    ["index" => "history", "name" => "History", "url" => "/api/2014/skills/history"],
    ["index" => "insight", "name" => "Insight", "url" => "/api/2014/skills/insight"],
    ["index" => "intimidation", "name" => "Intimidation", "url" => "/api/2014/skills/intimidation"],
    ["index" => "investigation", "name" => "Investigation", "url" => "/api/2014/skills/investigation"],
    ["index" => "medicine", "name" => "Medicine", "url" => "/api/2014/skills/medicine"],
    ["index" => "nature", "name" => "Nature", "url" => "/api/2014/skills/nature"],
    ["index" => "perception", "name" => "Perception", "url" => "/api/2014/skills/perception"],
    ["index" => "performance", "name" => "Performance", "url" => "/api/2014/skills/performance"],
    ["index" => "persuasion", "name" => "Persuasion", "url" => "/api/2014/skills/persuasion"],
    ["index" => "religion", "name" => "Religion", "url" => "/api/2014/skills/religion"],
    ["index" => "sleight-of-hand", "name" => "Sleight of Hand", "url" => "/api/2014/skills/sleight-of-hand"],
    ["index" => "stealth", "name" => "Stealth", "url" => "/api/2014/skills/stealth"],
    ["index" => "survival", "name" => "Survival", "url" => "/api/2014/skills/survival"]
];

$valores_skills_sql = [];
$importadas = 0;

foreach ($skills_data as $skill_meta) {
    $skill_url = "https://www.dnd5eapi.co" . $skill_meta['url'];
    
    $json_detalle = @file_get_contents($skill_url);
    if (!$json_detalle) {
        echo "<small style='color:orange;'>[Advertencia] No se pudo leer el detalle para: {$skill_meta['name']}</small><br>";
        continue;
    }
    
    $skill = json_decode($json_detalle, true);

    $index_api = $skill['index'];
    $name = $skill['name'];
    
    // Unimos los párrafos de la descripción si vienen en array
    $desc = "";
    if (isset($skill['desc']) && is_array($skill['desc'])) {
        $desc = implode("\n\n", $skill['desc']);
    }

    // Obtenemos el index del ability score asociado (ej: 'dex', 'wis', 'int', etc.)
    $ability_score_index = isset($skill['ability_score']['index']) ? $skill['ability_score']['index'] : '';

    // 1. Insertar o actualizar en la BD local
    $stmt_insert = $conexion->prepare("INSERT INTO skills (index_api, name, descripcion, ability_score_index) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE name=VALUES(name), descripcion=VALUES(descripcion), ability_score_index=VALUES(ability_score_index)");
    $stmt_insert->bind_param("ssss", $index_api, $name, $desc, $ability_score_index);
    $stmt_insert->execute();
    $stmt_insert->close();

    // 2. Acumular datos limpios para el archivo SQL de respaldo
    $index_sql = $conexion->real_escape_string($index_api);
    $name_sql = $conexion->real_escape_string($name);
    $desc_sql = $conexion->real_escape_string($desc);
    $ability_sql = $conexion->real_escape_string($ability_score_index);

    $valores_skills_sql[] = "('{$index_sql}', '{$name_sql}', '{$desc_sql}', '{$ability_sql}')";

    $importadas++;
    echo "<small style='color:green;'>[Procesada] Skill: {$name} (Atributo: {$ability_score_index})</small><br>";
}

// 3. Generar y guardar el archivo cargarSkills.sql en la carpeta /database
$directorio_db = __DIR__ . '/database';
if (!is_dir($directorio_db)) {
    mkdir($directorio_db, 0777, true);
}

$ruta_archivo = $directorio_db . '/cargarSkills.sql';

$contenido_sql = "-- Archivo de respaldo y carga masiva de Skills (D&D 5e)\n"
               . "USE dyd_wikidot;\n\n"
               . "CREATE TABLE IF NOT EXISTS skills (\n"
               . "    id INT AUTO_INCREMENT PRIMARY KEY,\n"
               . "    index_api VARCHAR(100) UNIQUE NOT NULL,\n"
               . "    name VARCHAR(100) NOT NULL,\n"
               . "    descripcion TEXT NOT NULL,\n"
               . "    ability_score_index VARCHAR(50) NOT NULL\n"
               . ");\n\n"
               . "INSERT INTO skills (index_api, name, descripcion, ability_score_index) VALUES\n"
               . implode(",\n", $valores_skills_sql)
               . "\nON DUPLICATE KEY UPDATE name=VALUES(name), descripcion=VALUES(descripcion), ability_score_index=VALUES(ability_score_index);\n";

file_put_contents($ruta_archivo, $contenido_sql);

echo "<hr>";
echo "<h3 style='color:blue;'>¡Proceso de skills completado con éxito!</h3>";
echo "<p>Se procesaron e importaron <strong>{$importadas}</strong> skills a la base de datos.</p>";
echo "<p>Archivo SQL de respaldo guardado en: <strong>/database/cargarSkills.sql</strong></p>";
?>