<?php
// Incluimos la conexión a tu base de datos 'dnd_wikidot'
require_once 'includes/conexion.php';

// Aumentamos el tiempo límite por si la API tarda en responder para los 38 traits
set_time_limit(300);

// El array con los 38 traits que pasaste
$traits_data = [
    ["index" => "artificers-lore", "name" => "Artificer's Lore", "url" => "/api/2014/traits/artificers-lore"],
    ["index" => "brave", "name" => "Brave", "url" => "/api/2014/traits/brave"],
    ["index" => "breath-weapon", "name" => "Breath Weapon", "url" => "/api/2014/traits/breath-weapon"],
    ["index" => "damage-resistance", "name" => "Damage Resistance", "url" => "/api/2014/traits/damage-resistance"],
    ["index" => "darkvision", "name" => "Darkvision", "url" => "/api/2014/traits/darkvision"],
    ["index" => "draconic-ancestry", "name" => "Draconic Ancestry", "url" => "/api/2014/traits/draconic-ancestry"],
    ["index" => "draconic-ancestry-black", "name" => "Draconic Ancestry (Black)", "url" => "/api/2014/traits/draconic-ancestry-black"],
    ["index" => "draconic-ancestry-blue", "name" => "Draconic Ancestry (Blue)", "url" => "/api/2014/traits/draconic-ancestry-blue"],
    ["index" => "draconic-ancestry-brass", "name" => "Draconic Ancestry (Brass)", "url" => "/api/2014/traits/draconic-ancestry-brass"],
    ["index" => "draconic-ancestry-bronze", "name" => "Draconic Ancestry (Bronze)", "url" => "/api/2014/traits/draconic-ancestry-bronze"],
    ["index" => "draconic-ancestry-copper", "name" => "Draconic Ancestry (Copper)", "url" => "/api/2014/traits/draconic-ancestry-copper"],
    ["index" => "draconic-ancestry-gold", "name" => "Draconic Ancestry (Gold)", "url" => "/api/2014/traits/draconic-ancestry-gold"],
    ["index" => "draconic-ancestry-green", "name" => "Draconic Ancestry (Green)", "url" => "/api/2014/traits/draconic-ancestry-green"],
    ["index" => "draconic-ancestry-red", "name" => "Draconic Ancestry (Red)", "url" => "/api/2014/traits/draconic-ancestry-red"],
    ["index" => "draconic-ancestry-silver", "name" => "Draconic Ancestry (Silver)", "url" => "/api/2014/traits/draconic-ancestry-silver"],
    ["index" => "draconic-ancestry-white", "name" => "Draconic Ancestry (White)", "url" => "/api/2014/traits/draconic-ancestry-white"],
    ["index" => "dwarven-combat-training", "name" => "Dwarven Combat Training", "url" => "/api/2014/traits/dwarven-combat-training"],
    ["index" => "dwarven-resilience", "name" => "Dwarven Resilience", "url" => "/api/2014/traits/dwarven-resilience"],
    ["index" => "dwarven-toughness", "name" => "Dwarven Toughness", "url" => "/api/2014/traits/dwarven-toughness"],
    ["index" => "elf-weapon-training", "name" => "Elf Weapon Training", "url" => "/api/2014/traits/elf-weapon-training"],
    ["index" => "extra-language", "name" => "Extra Language", "url" => "/api/2014/traits/extra-language"],
    ["index" => "fey-ancestry", "name" => "Fey Ancestry", "url" => "/api/2014/traits/fey-ancestry"],
    ["index" => "gnome-cunning", "name" => "Gnome Cunning", "url" => "/api/2014/traits/gnome-cunning"],
    ["index" => "halfling-nimbleness", "name" => "Halfling Nimbleness", "url" => "/api/2014/traits/halfling-nimbleness"],
    ["index" => "hellish-resistance", "name" => "Hellish Resistance", "url" => "/api/2014/traits/hellish-resistance"],
    ["index" => "high-elf-cantrip", "name" => "High Elf Cantrip", "url" => "/api/2014/traits/high-elf-cantrip"],
    ["index" => "infernal-legacy", "name" => "Infernal Legacy", "url" => "/api/2014/traits/infernal-legacy"],
    ["index" => "keen-senses", "name" => "Keen Senses", "url" => "/api/2014/traits/keen-senses"],
    ["index" => "lucky", "name" => "Lucky", "url" => "/api/2014/traits/lucky"],
    ["index" => "menacing", "name" => "Menacing", "url" => "/api/2014/traits/menacing"],
    ["index" => "naturally-stealthy", "name" => "Naturally Stealthy", "url" => "/api/2014/traits/naturally-stealthy"],
    ["index" => "relentless-endurance", "name" => "Relentless Endurance", "url" => "/api/2014/traits/relentless-endurance"],
    ["index" => "savage-attacks", "name" => "Savage Attacks", "url" => "/api/2014/traits/savage-attacks"],
    ["index" => "skill-versatility", "name" => "Skill Versatility", "url" => "/api/2014/traits/skill-versatility"],
    ["index" => "stonecunning", "name" => "Stonecunning", "url" => "/api/2014/traits/stonecunning"],
    ["index" => "tinker", "name" => "Tinker", "url" => "/api/2014/traits/tinker"],
    ["index" => "tool-proficiency", "name" => "Tool Proficiency", "url" => "/api/2014/traits/tool-proficiency"],
    ["index" => "trance", "name" => "Trance", "url" => "/api/2014/traits/trance"]
];

echo "<h2>Procesando y descargando descripciones de traits...</h2>";

$valores_sql = [];
$importados = 0;

foreach ($traits_data as $t) {
    $index_api = $t['index'];
    $nombre = $t['name'];
    $url_detalle = "https://www.dnd5eapi.co" . $t['url'];

    // Consultar el detalle para obtener la descripción
    $json_detalle = @file_get_contents($url_detalle);
    $desc = "";

    if ($json_detalle) {
        $detalle_data = json_decode($json_detalle, true);
        if (isset($detalle_data['desc'])) {
            $desc = implode("\n\n", $detalle_data['desc']);
        }
    }

    // 1. Insertar directamente en la base de datos local usando la conexión
    $stmt_insert = $conexion->prepare("INSERT INTO traits (index_api, nombre, descripcion) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE nombre=VALUES(nombre), descripcion=VALUES(descripcion)");
    $stmt_insert->bind_param("sss", $index_api, $nombre, $desc);
    $stmt_insert->execute();
    $stmt_insert->close();
    $importados++;

    // 2. Preparar los datos limpios para el archivo SQL
    $index_sql = $conexion->real_escape_string($index_api);
    $nombre_sql = $conexion->real_escape_string($nombre);
    $desc_sql = $conexion->real_escape_string($desc);

    $valores_sql[] = "('{$index_sql}', '{$nombre_sql}', '{$desc_sql}')";
    
    echo "<small style='color:green;'>[Procesado] {$nombre}</small><br>";
}

// 3. Generar y guardar el archivo cargarTraits.sql en la carpeta /database
$directorio_db = __DIR__ . '/../database';
if (!is_dir($directorio_db)) {
    mkdir($directorio_db, 0777, true);
}

$ruta_archivo = $directorio_db . '/cargarTraits.sql';
$contenido_sql = "-- Archivo de respaldo y carga masiva de Traits (D&D 5e)\n"
               . "USE dyd_wikidot;\n\n"
               . "CREATE TABLE IF NOT EXISTS traits (\n"
               . "    id INT AUTO_INCREMENT PRIMARY KEY,\n"
               . "    index_api VARCHAR(100) UNIQUE NOT NULL,\n"
               . "    nombre VARCHAR(150) NOT NULL,\n"
               . "    descripcion TEXT NOT NULL\n"
               . ");\n\n"
               . "INSERT INTO traits (index_api, nombre, descripcion) VALUES\n" 
               . implode(",\n", $valores_sql) 
               . "\nON DUPLICATE KEY UPDATE nombre=VALUES(nombre), descripcion=VALUES(descripcion);\n";

file_put_contents($ruta_archivo, $contenido_sql);

echo "<hr>";
echo "<h3 style='color:blue;'>¡Proceso completado con éxito!</h3>";
echo "<p>Se guardaron {$importados} traits en la base de datos.</p>";
echo "<p>Archivo SQL de respaldo generado correctamente en: <strong>/database/cargarTraits.sql</strong></p>";
?>