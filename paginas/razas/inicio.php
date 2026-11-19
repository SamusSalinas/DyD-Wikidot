<?php
require_once __DIR__ . '/../../includes/conexion.php';

$sql = "SELECT id, name, speed FROM razas ORDER BY id ASC";
$resultado = $conexion->query($sql);
?>

<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="fw-bold text-dark">Razas disponibles</h2>
            <p class="text-muted mb-0">Seleccioná una raza para consultar sus atributos, velocidad y rasgos principales:</p>
        </div>
    </div>
    <hr class="mb-4">

    <div class="row g-3">
        <?php while ($fila = $resultado->fetch_assoc()): ?>
            <?php 
                $raza_id = $fila['id'];
                
                // Consultamos los bonos de característica específicos para esta raza
                $sql_bonos = "SELECT a.index_api, a.nombre, rb.valor 
                              FROM raza_bonus rb 
                              JOIN ability_scores a ON rb.ability_score_id = a.id 
                              WHERE rb.raza_id = ?";
                
                $stmt_bonos = $conexion->prepare($sql_bonos);
                $stmt_bonos->bind_param("i", $raza_id);
                $stmt_bonos->execute();
                $resultado_bonos = $stmt_bonos->get_result();
            ?>
            <div class="col-md-4 col-sm-6">
                <!-- Card con el mismo estilo, borde personalizado y la esquina redondeada -->
                <div class="card h-100 shadow-sm position-relative border-success transition-card" style="border-radius: 4px 20px 4px 4px;">
                    <div class="card-body d-flex flex-column justify-content-between text-center py-4">
                        
                        <div>
                            <!-- Título de la raza con enlace extendido (stretched-link) -->
                            <h4 class="card-title h5 mb-2">
                                <a href="index.php?page=razas/ver_raza&id=<?php echo (int) $fila['id']; ?>" class="stretched-link text-decoration-none text-success">
                                    <?php echo htmlspecialchars($fila['name'], ENT_QUOTES, 'UTF-8'); ?>
                                </a>
                            </h4>
                        </div>

                        <!-- Contenedor dinámico de bonos de características y velocidad -->
                        <div class="mt-3 d-flex flex-column align-items-center justify-content-center gap-2">
                            
                            <!-- Bucle por cada bonus de la raza -->
                            <div class="d-flex flex-wrap justify-content-center gap-2">
                                <?php while ($bono = $resultado_bonos->fetch_assoc()): ?>
                                    <div class="d-flex align-items-center gap-1 bg-light px-2 py-1 rounded border" style="font-size: 0.8rem;">
                                        <!-- Opcional: Usamos el index_api del atributo para cargar un ícono dinámico -->
                                        <img src="images/<?php echo htmlspecialchars($bono['index_api'], ENT_QUOTES, 'UTF-8'); ?>.png" 
                                            alt="<?php echo htmlspecialchars($bono['nombre'], ENT_QUOTES, 'UTF-8'); ?>" 
                                            style="width: 16px; height: 16px; object-fit: contain;"
                                            onerror="this.src='images/strength.png';"> <!-- Imagen por defecto si falla -->
                                        <span class="text-dark"><strong><?php echo htmlspecialchars($bono['nombre'], ENT_QUOTES, 'UTF-8'); ?></strong></span>
                                        <span class="text-success fw-semibold">+<?php echo htmlspecialchars($bono['valor'], ENT_QUOTES, 'UTF-8'); ?></span>
                                    </div>
                                <?php endwhile; ?>
                                <?php $stmt_bonos->close(); ?>
                            </div>

                            <!-- Velocidad de la raza -->
                            <div class="text-muted mt-1" style="font-size: 0.75rem;">
                                Velocidad: <?php echo htmlspecialchars($fila['speed'], ENT_QUOTES, 'UTF-8'); ?> pies
                            </div>

                        </div>

                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<style>
    .transition-card {
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out, background-color 0.2s ease-in-out;
    }
    .transition-card:hover {
        transform: translateY(-3px);
        background-color: #f8f9fa;
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
    }
</style>