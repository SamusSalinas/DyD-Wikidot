<?php
require_once __DIR__ . '/../../includes/conexion.php';

// Tomamos el ID del trasfondo desde la URL (por defecto el trasfondo 1)
$id = isset($_GET['id']) ? (int)$_GET['id'] : 1;

// 1. Consultar la información principal del trasfondo
$sql = "SELECT * FROM trasfondos WHERE id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows > 0):
    $trasfondo = $resultado->fetch_assoc();
    $stmt->close();

    // 2. Consultar bonificadores de características (trasfondo_bonus -> ability_scores)
    $sql_bonos = "SELECT a.index_api, a.nombre
                  FROM trasfondo_bonus tb 
                  JOIN ability_scores a ON tb.ability_score_id = a.id 
                  WHERE tb.trasfondo_id = ?";
    $stmt_bonos = $conexion->prepare($sql_bonos);
    $stmt_bonos->bind_param("i", $id);
    $stmt_bonos->execute();
    $resultado_bonos = $stmt_bonos->get_result();

    // 3. Consultar proficiencias / competencias (trasfondo_proficiencia)
    // Ajustá el nombre de la tabla o campo si difiere sutilmente en tu base de datos
    $sql_prof = "SELECT p.nombre 
                 FROM trasfondo_proficiencias tp
                 JOIN proficiencias p ON tp.proficiencia_id = p.id 
                 WHERE tp.trasfondo_id = ?";
    $stmt_prof = $conexion->prepare($sql_prof);
    $stmt_prof->bind_param("i", $id);
    $stmt_prof->execute();
    $resultado_prof = $stmt_prof->get_result();

    // Separamos el equipo inicial si viene por comas
    $equipo_texto = $trasfondo['equipment_desc'] ?? '';
    $lista_equipo = !empty($equipo_texto) ? explode(',', $equipo_texto) : [];
?>

<div class="container my-4">
    <!-- Encabezado del Trasfondo -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2><?php echo htmlspecialchars($trasfondo['name']); ?></h2>
            
        </div>
        <a href="index.php?page=trasfondos/inicio" class="btn btn-outline-secondary btn-sm">Volver a todos los trasfondos</a>
    </div>
    <hr>

    <!-- Tarjeta de Resumen -->
    <div class="card shadow-sm mb-4 border-success">
        <div class="card-header bg-success text-white fw-bold">Resumen del Trasfondo</div>
        <div class="card-body">
            
            <!-- Dote / Rasgo principal (feat_name) -->
            <?php if (!empty($trasfondo['feat_name'])): ?>
            <div class="row mb-3 align-items-center">
                <div class="col-md-4 fw-bold">Dote / Rasgo especial:</div>
                <div class="col-md-8">
                    <span class="badge bg-success px-3 py-2" style="font-size: 0.9rem;">
                        <?php echo htmlspecialchars($trasfondo['feat_name']); ?>
                    </span>
                </div>
            </div>
            <?php endif; ?>

            <!-- Bonificador de características (trasfondo_bonus) -->
            <div class="row mb-3 align-items-center">
                <div class="col-md-4 fw-bold">Bonificador de características:</div>
                <div class="col-md-8">
                    <div class="d-flex flex-wrap gap-2">
                        <?php if ($resultado_bonos->num_rows > 0): ?>
                            <?php while ($bono = $resultado_bonos->fetch_assoc()): ?>
                                <span class="badge bg-success d-flex align-items-center gap-1 px-2 py-1">
                                    <img src="images/attributes/<?php echo htmlspecialchars($bono['index_api']); ?>.png" alt="" style="width: 14px; height: 14px;" onerror="this.style.display='none';">
                                    <?php echo htmlspecialchars($bono['nombre']); ?>
                                </span>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <span class="text-muted small">Sin bonificadores fijos.</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Competencias / Proficiencias (trasfondo_proficiencia) -->
            <div class="row mb-2">
                <div class="col-md-4 fw-bold">Competencias y proficiencias:</div>
                <div class="col-md-8">
                    <div class="d-flex flex-wrap gap-2">
                        <?php if ($resultado_prof->num_rows > 0): ?>
                            <?php while ($prof = $resultado_prof->fetch_assoc()): ?>
                                <span class="badge bg-light text-dark border px-2 py-1" style="font-size: 0.85rem;">
                                    <?php echo htmlspecialchars($prof['nombre']); ?>
                                </span>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <span class="text-muted small">Sin competencias adicionales registradas.</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Equipamiento Inicial -->
    <?php if (!empty($lista_equipo)): ?>
        <h3 class="h4 mt-4 mb-3">Equipamiento inicial</h3>
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <ul class="mb-0">
                    <?php foreach ($lista_equipo as $item): ?>
                        <li class="py-1"><?php echo htmlspecialchars(trim(rtrim($item, '.'))); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    <?php endif; ?>

    <div class="mt-4">
        <a href="index.php?page=trasfondos/inicio" class="btn btn-secondary">Volver a todos los trasfondos</a>
    </div>
</div>

<?php 


?>
    
<?php endif; ?>