<?php
// ==========================================
// CONFIGURACIÓN: Cambiar según la clase que quieras respaldar
// (ej: 'wizard', 'cleric', 'sorcerer', 'bard', 'druid', 'paladin', 'ranger', 'warlock', 'fighter', 'monk', 'rogue', 'barbarian')
// ==========================================
$slug_clase = 'barbarian'; // <-- Nombralo solo con el nombre de la clase en minúscula

echo "<h2>Descargando listado para la clase: {$slug_clase}</h2>";

$url_api = "https://www.dnd5eapi.co/api/classes/{$slug_clase}/spells";
$json_datos = @file_get_contents($url_api);

if (!$json_datos) {
    die("<p style='color:red;'>Error al conectar con la API para la clase {$slug_clase}. Verificá el nombre.</p>");
}

// Guardamos el JSON crudo directamente en un archivo local en una carpeta llamada 'json_clases'
$carpeta = 'json_clases';
if (!file_exists($carpeta)) {
    mkdir($carpeta, 0777, true);
}

$archivo_destino = "{$carpeta}/{$slug_clase}.json";
file_put_contents($archivo_destino, $json_datos);

$data = json_decode($json_datos, true);
$cantidad = isset($data['results']) ? count($data['results']) : 0;

echo "<h3 style='color:green;'>¡Listado guardado con éxito!</h3>";
echo "<p>Se guardaron <strong>{$cantidad}</strong> hechizos en el archivo local: <code>{$archivo_destino}</code></p>";
echo "<p>Ya podés cambiar la variable <code>\$slug_clase</code> para descargar otra clase o pasar al siguiente paso para procesarlos.</p>";
?>