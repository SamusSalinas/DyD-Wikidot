<?php
// Control de sesión y conexión
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}
require_once __DIR__ . '/../../includes/conexion.php';

// Consultar las dotes ordenadas por id
$sql = "SELECT id, nombre, prerequisito FROM dotes ORDER BY id ASC";
$resultado = $conexion->query($sql);
?>

<div class="container py-4">
    <!-- Cabecera de la sección -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="fw-bold text-dark">Dotes de D&amp;D 5e</h2>
            <p class="text-muted mb-0">Seleccioná una dote para consultar sus prerrequisitos y beneficios detallados.</p>
        </div>
    </div>
    <hr class="mb-4">

    <?php if ($resultado && $resultado->num_rows > 0): ?>
        <!-- Cuadrícula de tarjetas responsiva -->
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php while ($fila = $resultado->fetch_assoc()): ?>
                <?php
                $prerequisito = trim($fila['prerequisito'] ?? '');
                $tiene_prereq = !empty($prerequisito);
                ?>
                <div class="col">
                    <div class="card h-100 shadow-sm border-success-subtle transition-card" style="background-color: #fbfdfb;">
                        <div class="card-body d-flex flex-column justify-content-between">
                            <div>
                                <h4 class="card-title fw-bold mb-2 text-success">
                                    <?php echo htmlspecialchars($fila['nombre'], ENT_QUOTES, 'UTF-8'); ?>
                                </h4>
                                <hr class="my-2">

                                <div class="mb-3 mt-2">
                                    <span class="fw-bold small text-muted d-block mb-1">Prerrequisito:</span>
                                    <?php if ($tiene_prereq): ?>
                                        <span class="badge text-success border border-success-subtle px-2 py-1" style="background-color: #eef7f1; font-size: 0.82rem; white-space: normal; text-align: left;">
                                            <?php echo htmlspecialchars($prerequisito, ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-light text-muted border px-2 py-1" style="font-size: 0.82rem;">
                                            Ninguno
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="mt-2">
                                <a href="index.php?page=dotes/ver_dote&id=<?php echo (int)$fila['id']; ?>" class="btn btn-outline-success btn-sm w-100 fw-bold" style="--bs-btn-hover-bg: #198754; --bs-btn-border-color: #75b798; --bs-btn-color: #1e6b48;">
                                    Ver Detalle <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <!-- Mensaje en caso de que no haya dotes cargadas -->
        <div class="card shadow-sm border-success-subtle text-center py-5" style="background-color: #fbfdfb;">
            <div class="card-body">
                <span class="display-4 text-success">📜</span>
                <h4 class="fw-bold mt-3 text-success">No hay dotes registradas todavía</h4>
                <p class="text-muted mb-0">Pronto se agregarán nuevas opciones al compendio.</p>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
.transition-card {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}
.transition-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 .5rem 1rem rgba(25, 135, 84, 0.12) !important;
}
</style>