<?php
require_once __DIR__ . '/../../includes/conexion.php';

// Consultamos todos los trasfondos ordenados por ID
$sql = "SELECT id, index_api, name, feat_name, equipment_desc FROM trasfondos ORDER BY id ASC";
$resultado = $conexion->query($sql);
?>

<div class="container my-4">
    <!-- Encabezado de la sección -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="fw-bold text-dark">Trasfondos disponibles</h2>
            <p class="text-muted mb-0">Seleccioná un trasfondo para consultar sus dotes y equipamiento inicial.</p>
        </div>
    </div>
    <hr class="mb-4">

    <!-- Grilla de Tarjetas -->
    <?php if ($resultado && $resultado->num_rows > 0): ?>
        <div class="row g-4">
            <?php while ($fila = $resultado->fetch_assoc()): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm border-success-subtle transition-card" style="background-color: #fbfdfb;">
                        <div class="card-header bg-success-subtle text-success fw-bold border-bottom border-success-subtle py-3">
                            <?php echo htmlspecialchars($fila['name'], ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                        <div class="card-body d-flex flex-column justify-content-between">
                            <div>
                                <!-- Dote / Rasgo principal -->
                                <div class="mb-3">
                                    <span class="fw-bold small text-muted d-block">Dote / Rasgo especial:</span>
                                    <span class="badge text-success border border-success-subtle px-2 py-1 mt-1" style="background-color: #eef7f1; font-size: 0.85rem;">
                                        <?php echo htmlspecialchars($fila['feat_name'], ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                </div>
                            </div>

                            <!-- Botón de Acceso -->
                            <div class="mt-3">
                                <a href="index.php?page=trasfondos/ver_trasfondo&id=<?php echo (int) $fila['id']; ?>" class="btn btn-outline-success btn-sm w-100 fw-bold" style="--bs-btn-hover-bg: #198754; --bs-btn-border-color: #75b798; --bs-btn-color: #1e6b48;">
                                    Ver detalle completo →
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="alert border text-success text-center p-4" style="background-color: #eef7f1; border-color: #c7e6d3 !important;">
            No hay trasfondos registrados en la base de datos todavía.
        </div>
    <?php endif; ?>
</div>

<?php 
if ($resultado) {
    $resultado->free();
}
?>

<style>
    .transition-card {
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }
    .transition-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 .5rem 1rem rgba(25, 135, 84, 0.12)!important;
    }
</style>