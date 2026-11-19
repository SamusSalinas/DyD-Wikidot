<?php
// Clases mágicas oficiales con nombre en español, inglés
$clases_magicas = [
    'bard' => ['nombre' => 'Bardo', 'ingles' => 'Bard'],
    'cleric' => ['nombre' => 'Clérigo', 'ingles' => 'Cleric'],
    'druid' => ['nombre' => 'Druida', 'ingles' => 'Druid'],
    'paladin' => ['nombre' => 'Paladín', 'ingles' => 'Paladin'],
    'ranger' => ['nombre' => 'Explorador', 'ingles' => 'Ranger'],
    'sorcerer' => ['nombre' => 'Hechicero', 'ingles' => 'Sorcerer'],
    'warlock' => ['nombre' => 'Brujo', 'ingles' => 'Warlock'],
    'wizard' => ['nombre' => 'Mago', 'ingles' => 'Wizard']
];
?>

<div class="container my-4">
    <!-- Encabezado de la sección -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="fw-bold text-dark">Listas de Hechizos por Clase</h2>
            <p class="text-muted mb-0">Seleccioná una clase mágica para explorar su grimorio completo organizado por nivel de conjuro.</p>
        </div>
    </div>
    <hr class="mb-4">

    <!-- Grilla de Clases Mágicas -->
    <div class="row g-4">
        <?php foreach ($clases_magicas as $slug => $info):
            $icono_slug = strtolower(trim((string) $slug));
        ?>
            <div class="col-md-4 col-sm-6">
                <div class="card h-100 shadow-sm border-success transition-card" style="background-color: #fbfdfb;">
                    <div class="card-body d-flex flex-column justify-content-between">
                        <div>
                            <!-- Ícono a la izquierda + Nombre de la clase como enlace -->
                            <h4 class="card-title d-flex align-items-center gap-2 mb-1">
                                <img src="images/<?php echo htmlspecialchars($icono_slug, ENT_QUOTES, 'UTF-8'); ?>.png"
                                     alt="<?php echo htmlspecialchars($info['nombre'], ENT_QUOTES, 'UTF-8'); ?>"
                                     style="width: 64px; height: 64px; object-fit: contain; flex-shrink: 0;"
                                     onerror="this.style.display='none';">
                                <a href="index.php?page=hechizos/ver_clase&clase=<?php echo urlencode($icono_slug); ?>"
                                   class="text-success text-decoration-none stretched-link">
                                    <?php echo htmlspecialchars($info['nombre'], ENT_QUOTES, 'UTF-8'); ?>
                                </a>
                            </h4>
                            
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<style>
    .transition-card {
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }
    .transition-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
    }
</style>