<?php
// Incluimos la conexión a tu base de datos 'dnd_wikidot'
require_once 'includes/conexion.php';

// Aumentamos el tiempo límite por seguridad
set_time_limit(300);

// ==========================================
// CONFIGURACIÓN: Nombre de la clase (debe coincidir con el archivo JSON del Paso 1)
// ==========================================


/**
 * LISTA DE CLASES OFICIALES DE D&D 5e EN ESPAÑOL
 *
 * 1. Clérigo (cleric)[cite: 1]
 * 2. Brujo (warlock)[cite: 1]
 * 3. Hechicero (sorcerer)[cite: 1]
 * 4. Mago (wizard)[cite: 1]
 * 5. Artífice (artificer)[cite: 1]
 * 6. Bardo (bard)[cite: 1]
 * 7. Druida (druid)[cite: 1]
 * 8. Pícaro (rogue)[cite: 1]
 * 9. Guerrero (fighter)[cite: 1]
 * 10. Bárbaro (barbarian)[cite: 1]
 * 11. Monje (monk)[cite: 1]
 * 12. Paladín (paladin)
 * 13. Explorador / ranger
 */



$slug_clase = 'ranger'; // Nombre del archivo local (ej: json_clases/wizard.json)
$nombre_clase_espanol = 'Explorador  '; // Nombre prolijo para guardar en la BD

$archivo_json = "json_clases/{$slug_clase}.json";

if (!file_exists($archivo_json)) {
    die("<p style='color:red;'>Error: No se encontró el archivo local para la clase '{$slug_clase}'. Ejecutá primero el Paso 1.</p>");
}

echo "<h2>Procesando hechizos desde el archivo local para: {$nombre_clase_espanol} ({$slug_clase})</h2>";

// Leemos el contenido del archivo JSON local
$json_contenido = file_get_contents($archivo_json);
$data = json_decode($json_contenido, true);

if (!isset($data['results'])) {
    die("<p style='color:red;'>Error: El archivo JSON no tiene el formato esperado.</p>");
}

$spells = $data['results'];
$importados = 0;
$actualizados = 0;
$existentes_locales = 0;

echo "<p>Total de hechizos en el archivo local: " . count($spells) . ". Verificando base de datos...</p>";

foreach ($spells as $spell_meta) {
    $nombre = $spell_meta['name'];
    $url_relativa = $spell_meta['url']; // Ej: /api/spells/fire-bolt

    // 1. Consultar PRIMERO en la base de datos local si el hechizo ya existe
    $stmt_check = $conexion->prepare("SELECT id, clases_permitidas FROM hechizos WHERE nombre = ?");
    $stmt_check->bind_param("s", $nombre);
    $stmt_check->execute();
    $res_check = $stmt_check->get_result();

    if ($res_check->num_rows > 0) {
        // El hechizo ya existe en la DB local
        $row = $res_check->fetch_assoc();
        $id_hechizo = $row['id'];
        $clases_actuales = $row['clases_permitidas'];

        // Verificamos si la clase ya está en la lista de permitidas
        if (strpos($clases_actuales, $nombre_clase_espanol) === false) {
            $nuevas_clases = $clases_actuales . ", " . $nombre_clase_espanol;
            $stmt_update = $conexion->prepare("UPDATE hechizos SET clases_permitidas = ? WHERE id = ?");
            $stmt_update->bind_param("si", $nuevas_clases, $id_hechizo);
            $stmt_update->execute();
            $stmt_update->close();
            $actualizados++;
        } else {
            $existentes_locales++;
        }
        $stmt_check->close();
        continue; // Pasamos al siguiente sin ir a internet
    }
    $stmt_check->close();

    // 2. Si NO existe en la DB local, recién ahí vamos a internet a buscar su detalle
    $spell_url = "https://www.dnd5eapi.co" . $url_relativa;
    $json_detalle = @file_get_contents($spell_url);

    if (!$json_detalle) {
        echo "<p style='color:orange;'>No se pudo descargar el detalle de: {$nombre}</p>";
        continue;
    }

    $spell_data = json_decode($json_detalle, true);

    $nivel = isset($spell_data['level']) ? $spell_data['level'] : 0;
    $escuela = isset($spell_data['school']['name']) ? $spell_data['school']['name'] : 'Desconocida';
    $descripcion = isset($spell_data['desc']) ? implode("\n\n", $spell_data['desc']) : '';

    // 3. Subirlo INMEDIATAMENTE a la base de datos local (uno por uno)
    $stmt_insert = $conexion->prepare("INSERT INTO hechizos (nombre, nivel, escuela, clases_permitidas, descripcion) VALUES (?, ?, ?, ?, ?)");
    $stmt_insert->bind_param("sisss", $nombre, $nivel, $escuela, $nombre_clase_espanol, $descripcion);
    $stmt_insert->execute();
    $stmt_insert->close();

    $importados++;
    echo "<p style='color:green;'>[Guardado en DB] {$nombre} (Nivel {$nivel})</p>";

    // Forzamos la salida en pantalla para ver el progreso en tiempo real
    flush();
    ob_flush();
}

echo "<hr>";
echo "<h3 style='color:blue;'>¡Proceso de la clase {$nombre_clase_espanol} finalizado!</h3>";
echo "<ul>";
echo "<li>Nuevos hechizos descargados e insertados de inmediato: <strong>{$importados}</strong></li>";
echo "<li>Hechizos ya existentes a los que se les sumó la clase: <strong>{$actualizados}</strong></li>";
echo "<li>Hechizos que ya estaban completos: <strong>{$existentes_locales}</strong></li>";
echo "</ul>";
echo "<p>Ya podés cambiar la variable <code>\$slug_clase</code> y <code>\$nombre_clase_espanol</code> para procesar otra clase desde su archivo local.</p>";
?>