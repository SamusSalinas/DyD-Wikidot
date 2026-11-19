<?php
require_once __DIR__ . '/../../includes/conexion.php';

// Tomamos el ID de la raza desde la URL (por defecto la raza 1)
$id = isset($_GET['id']) ? (int)$_GET['id'] : 1;

// 1. Consultar la raza principal
$sql = "SELECT * FROM razas WHERE id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows > 0):
    $raza = $resultado->fetch_assoc();
    $stmt->close();

    // 2. Consultar bonificadores de características de la raza principal (raza_bonus)
    $sql_bonos = "SELECT a.index_api, a.nombre, rb.valor 
                  FROM raza_bonus rb 
                  JOIN ability_scores a ON rb.ability_score_id = a.id 
                  WHERE rb.raza_id = ?";
    $stmt_bonos = $conexion->prepare($sql_bonos);
    $stmt_bonos->bind_param("i", $id);
    $stmt_bonos->execute();
    $resultado_bonos = $stmt_bonos->get_result();

    // 3. Consultar rasgos raciales de la raza principal (raza_trait -> traits)
    $sql_traits = "SELECT t.index_api, t.nombre, t.descripcion 
                   FROM raza_trait rt 
                   JOIN traits t ON rt.trait_id = t.id 
                   WHERE rt.raza_id = ?";
    $stmt_traits = $conexion->prepare($sql_traits);
    $stmt_traits->bind_param("i", $id);
    $stmt_traits->execute();
    $resultado_traits = $stmt_traits->get_result();

    // 4. Consultar subrazas asociadas a esta raza
    $sql_subrazas = "SELECT * FROM subrazas WHERE raza_id = ?";
    $stmt_subrazas = $conexion->prepare($sql_subrazas);
    $stmt_subrazas->bind_param("i", $id);
    $stmt_subrazas->execute();
    $resultado_subrazas = $stmt_subrazas->get_result();
?>

<div class="container my-4">
    <!-- Encabezado de la Raza -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><?php echo htmlspecialchars($raza['name']); ?></h2>
        <a href="index.php?page=razas/inicio" class="btn btn-outline-secondary btn-sm">Volver a todas las razas</a>
    </div>
    <hr>

    <!-- Tarjeta de Resumen -->
    <div class="card shadow-sm mb-4" style="border-color: #2e4d3b;">
        <div class="card-header bg-light text-dark fw-bold">Resumen de la raza</div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-4 fw-bold">Bonificador de características:</div>
                <div class="col-md-8">
                    <div class="d-flex flex-wrap gap-2">
                        <?php while ($bono = $resultado_bonos->fetch_assoc()): ?>
                            <span class="badge bg-success d-flex align-items-center gap-1 px-2 py-1">
                                <img src="images/attributes/<?php echo htmlspecialchars($bono['index_api']); ?>.png" alt="" style="width: 14px; height: 14px;" onerror="this.style.display='none';">
                                <?php echo htmlspecialchars($bono['nombre']); ?> +<?php echo htmlspecialchars($bono['valor']); ?>
                            </span>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-md-4 fw-bold">Velocidad base:</div>
                <div class="col-md-8"><?php echo htmlspecialchars($raza['speed']); ?> pies</div>
            </div>

            <div class="row mb-2">
                <div class="col-md-4 fw-bold">Tamaño:</div>
                <div class="col-md-8"><?php echo htmlspecialchars($raza['size']); ?> - <?php echo htmlspecialchars($raza['size_description']); ?></div>
            </div>

            <div class="row mb-2">
                <div class="col-md-4 fw-bold">Idiomas:</div>
                <div class="col-md-8"><?php echo htmlspecialchars($raza['languages']); ?></div>
            </div>
            
            <?php if (!empty($raza['language_desc'])): ?>
            <div class="row mb-2">
                <div class="col-md-4 fw-bold">Detalle de idiomas:</div>
                <div class="col-md-8 text-muted small"><?php echo htmlspecialchars($raza['language_desc']); ?></div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Rasgos raciales con Hipervínculos internos -->
    <h3 class="h4 mt-4 mb-3">Rasgos raciales</h3>
    <ul class="list-group mb-4">
        <?php while ($trait = $resultado_traits->fetch_assoc()): ?>
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <span><?php echo htmlspecialchars($trait['nombre']); ?></span>
                <a href="#trait-<?php echo $trait['index_api']; ?>" class="btn btn-sm btn-outline-success">Ver detalle ↓</a>
            </li>
        <?php endwhile; ?>
    </ul>

    <!-- Subrazas asociadas completas -->
    <?php if ($resultado_subrazas->num_rows > 0): ?>
        <h3 class="h4 mt-4 mb-3">Subrazas</h3>
        <div class="row g-4 mb-4">
            <?php while ($subraza = $resultado_subrazas->fetch_assoc()): 
                $subraza_id = $subraza['id'];

                // Consultar bonos específicos de esta subraza
                $sql_sub_bonos = "SELECT a.index_api, a.nombre, sb.valor 
                                  FROM subraza_bonus sb 
                                  JOIN ability_scores a ON sb.ability_score_id = a.id 
                                  WHERE sb.subraza_id = ?";
                $stmt_sb = $conexion->prepare($sql_sub_bonos);
                $stmt_sb->bind_param("i", $subraza_id);
                $stmt_sb->execute();
                $res_sb = $stmt_sb->get_result();

                // Consultar rasgos específicos de esta subraza
                $sql_sub_traits = "SELECT t.index_api, t.nombre, t.descripcion 
                                   FROM subraza_trait st 
                                   JOIN traits t ON st.trait_id = t.id 
                                   WHERE st.subraza_id = ?";
                $stmt_st = $conexion->prepare($sql_sub_traits);
                $stmt_st->bind_param("i", $subraza_id);
                $stmt_st->execute();
                $res_st = $stmt_st->get_result();
            ?>
                <div class="col-12">
                    <div class="card border-success shadow-sm">
                        <div class="card-header bg-success text-white fw-bold">
                            <?php echo htmlspecialchars($subraza['name']); ?> (Subraza)
                        </div>
                        <div class="card-body">
                            <!-- Bonos de subraza -->
                            <div class="mb-3">
                                <span class="fw-bold small text-muted">Bonificador adicional:</span>
                                <div class="d-flex flex-wrap gap-2 mt-1">
                                    <?php while ($sub_bono = $res_sb->fetch_assoc()): ?>
                                        <span class="badge bg-light text-dark border d-flex align-items-center gap-1 px-2 py-1">
                                            <img src="images/attributes/<?php echo htmlspecialchars($sub_bono['index_api']); ?>.png" alt="" style="width: 14px; height: 14px;" onerror="this.style.display='none';">
                                            <?php echo htmlspecialchars($sub_bono['nombre']); ?> +<?php echo htmlspecialchars($sub_bono['valor']); ?>
                                        </span>
                                    <?php endwhile; $stmt_sb->close(); ?>
                                </div>
                            </div>

                            <!-- Velocidad / Edad / Idiomas de la subraza si aplican -->
                            <?php if (!empty($subraza['speed'])): ?>
                                <p class="card-text small mb-1"><strong>Velocidad:</strong> <?php echo $subraza['speed']; ?> pies</p>
                            <?php endif; ?>
                            
                            <?php if (!empty($subraza['languages'])): ?>
                                <p class="card-text small mb-1"><strong>Idiomas adicionales:</strong> <?php echo htmlspecialchars($subraza['languages']); ?></p>
                            <?php endif; ?>

                            <?php if (!empty($subraza['age'])): ?>
                                <p class="card-text small text-muted mb-2"><strong>Edad:</strong> <?php echo htmlspecialchars($subraza['age']); ?></p>
                            <?php endif; ?>

                            <!-- Rasgos de la subraza -->
                            <?php if ($res_st->num_rows > 0): ?>
                                <div class="mt-3">
                                    <span class="fw-bold small text-muted d-block mb-2">Rasgos de la subraza:</span>
                                    <ul class="list-group">
                                        <?php while ($sub_trait = $res_st->fetch_assoc()): ?>
                                            <li class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-2">
                                                <span class="small"><strong><?php echo htmlspecialchars($sub_trait['nombre']); ?></strong></span>
                                                <a href="#trait-<?php echo $sub_trait['index_api']; ?>" class="btn btn-xs btn-outline-success" style="font-size: 0.75rem; padding: 2px 6px;">Ver detalle ↓</a>
                                            </li>
                                        <?php endwhile; ?>
                                    </ul>
                                </div>
                            <?php endif; $stmt_st->close(); ?>

                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>

    <!-- Sección ampliada de Descripción de Rasgos (Destino de los hipervínculos de raza y subrazas) -->
    <h3 class="h4 mt-5 mb-3">Detalle de Rasgos</h3>
    <?php 
    // Recorremos los rasgos de la raza principal
    $stmt_traits->execute();
    $resultado_traits_detalle = $stmt_traits->get_result();
    while ($trait_det = $resultado_traits_detalle->fetch_assoc()): 
    ?>
        <div id="trait-<?php echo $trait_det['index_api']; ?>" class="card mb-3 shadow-sm bg-light">
            <div class="card-body">
                <h5 class="card-title text-dark"><?php echo htmlspecialchars($trait_det['nombre']); ?></h5>
                <p class="card-text mb-0" style="font-size: 0.95rem; white-space: pre-line;"><?php echo htmlspecialchars($trait_det['descripcion']); ?></p>
            </div>
        </div>
    <?php endwhile; 

    // Recorremos también los rasgos de las subrazas para que el ancla los encuentre sin problemas
    $resultado_subrazas->data_seek(0);
    while ($sub_raza_det = $resultado_subrazas->fetch_assoc()):
        $sub_id_det = $sub_raza_det['id'];
        $sql_det_st = "SELECT t.index_api, t.nombre, t.descripcion 
                       FROM subraza_trait st 
                       JOIN traits t ON st.trait_id = t.id 
                       WHERE st.subraza_id = ?";
        $stmt_dst = $conexion->prepare($sql_det_st);
        $stmt_dst->bind_param("i", $sub_id_det);
        $stmt_dst->execute();
        $res_dst = $stmt_dst->get_result();
        while ($sub_trait_det = $res_dst->fetch_assoc()):
    ?>
        <div id="trait-<?php echo $sub_trait_det['index_api']; ?>" class="card mb-3 shadow-sm bg-light border-success">
            <div class="card-body">
                <span class="badge bg-success mb-1">Subraza: <?php echo htmlspecialchars($sub_raza_det['name']); ?></span>
                <h5 class="card-title text-dark"><?php echo htmlspecialchars($sub_trait_det['nombre']); ?></h5>
                <p class="card-text mb-0" style="font-size: 0.95rem; white-space: pre-line;"><?php echo htmlspecialchars($sub_trait_det['descripcion']); ?></p>
            </div>
        </div>
    <?php 
        endwhile;
        $stmt_dst->close();
    endwhile; 
    ?>

    <div class="mt-4">
        <a href="index.php?page=razas/inicio" class="btn btn-secondary">Volver a todas las razas</a>
    </div>
</div>

<?php 
    $stmt_bonos->close();
    $stmt_traits->close();
    $stmt_subrazas->close();
else: 
?>
    <div class="container my-5 text-center">
        <h2>Raza no encontrada</h2>
        <p class="text-muted">No existe una raza con ese identificador en la base de datos.</p>
        <a href="index.php?page=razas/inicio" class="btn btn-primary mt-3">Volver a todas las razas</a>
    </div>
<?php endif; ?>