<?php
// Opcional: Podés definir las clases mágicas oficiales de forma estática o consultarlas de la BD
$clases_magicas = [
    'bard' => 'Bard',
    'cleric' => 'Cleric',
    'druid' => 'Druid',
    'paladin' => 'Paladin',
    'ranger' => 'Ranger',
    'sorcerer' => 'Sorcerer',
    'warlock' => 'Warlock',
    'wizard' => 'Wizard'
];
?>

<h2>Listas de Hechizos por Clase</h2>
<ul>
    <?php foreach ($clases_magicas as $slug => $nombre): ?>
        <li><a href="index.php?page=hechizos/ver_clase&clase=<?php echo $slug; ?>"><?php echo $nombre; ?></a></li>
    <?php endforeach; ?>
</ul>