<?php
// Control de sesión y conexión
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}
require_once __DIR__ . '/../../includes/conexion.php';

// Consultar todos los personajes guardados
$sql = "SELECT id, nombre, jugador, raza, clase, subclase, nivel, fecha_creacion FROM personajes ORDER BY id DESC";
$resultado = $conexion->query($sql);
?>

<div class="container py-4">
    <div class="row mb-4 align-items-center">
        <div class="col">
            <h2 class="fw-bold text-dark">📜 Crónicas de Aventureros</h2>
            <p class="text-muted mb-0">Listado de todos los personajes registrados en el sistema.</p>
        </div>
        <div class="col-auto">
            <a href="index.php?page=personajes/hoja&capitulo=1" class="btn btn-outline-success fw-bold" style="--bs-btn-hover-bg: #198754; --bs-btn-border-color: #75b798; --bs-btn-color: #1e6b48;">
                <i class="bi bi-plus-lg"></i> + Crear Nuevo Personaje
            </a>
        </div>
    </div>
    <hr class="mb-4">

    <?php if ($resultado && $resultado->num_rows > 0): ?>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php while ($pj = $resultado->fetch_assoc()): ?>
                <div class="col">
                    <div class="card h-100 shadow-sm border-success transition-card" style="background-color: #fbfdfb;">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h4 class="card-title fw-bold text-success mb-0">
                                    <?php echo htmlspecialchars($pj['nombre'], ENT_QUOTES, 'UTF-8'); ?>
                                </h4>
                                <span class="badge bg-success-subtle text-success border border-success-subtle">
                                    Nivel <?php echo (int)$pj['nivel']; ?>
                                </span>
                            </div>
                            
                            <h6 class="card-subtitle text-muted mb-3">
                                <?php echo htmlspecialchars($pj['clase'], ENT_QUOTES, 'UTF-8'); ?> 
                                <?php if (!empty($pj['subclase'])): ?>
                                    <small class="text-secondary">(<?php echo htmlspecialchars($pj['subclase'], ENT_QUOTES, 'UTF-8'); ?>)</small>
                                <?php endif; ?>
                            </h6>

                            <hr class="my-2">

                            <ul class="list-unstyled small mb-3 mt-2 text-secondary">
                                <li class="mb-1"><strong>Raza:</strong> <?php echo htmlspecialchars($pj['raza'], ENT_QUOTES, 'UTF-8'); ?></li>
                                <li class="mb-1"><strong>Jugador:</strong> <?php echo htmlspecialchars($pj['jugador'], ENT_QUOTES, 'UTF-8'); ?></li>
                                <li><i class="bi bi-calendar3"></i> Creado: <?php echo date('d/m/Y', strtotime($pj['fecha_creacion'])); ?></li>
                            </ul>
                        </div>
                        <div class="card-footer bg-transparent border-top-0 pb-3 pt-0 d-flex justify-content-between">
                            <a href="index.php?page=personajes/ver&id=<?php echo (int)$pj['id']; ?>" class="btn btn-outline-success btn-sm w-100 me-1 fw-bold" style="--bs-btn-hover-bg: #198754; --bs-btn-border-color: #75b798; --bs-btn-color: #1e6b48;">
                                Ver Ficha
                            </a>
                            <!-- Opcional si querés agregar botón de borrar/editar -->
                            <!-- <a href="index.php?page=personajes/eliminar&id=<?php echo (int)$pj['id']; ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('¿Seguro querés eliminar este personaje?');">🗑️</a> -->
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="card shadow-sm border-success-subtle text-center py-5" style="background-color: #fbfdfb;">
            <div class="card-body">
                <span class="display-4 text-success">🛡️</span>
                <h4 class="fw-bold mt-3 text-success">No hay personajes registrados todavía</h4>
                <p class="text-muted mb-4">¡Anímate a dar de alta al primero usando el asistente de creación!</p>
                <a href="index.php?page=personajes/hoja&capitulo=1" class="btn btn-outline-success fw-bold" style="--bs-btn-hover-bg: #198754; --bs-btn-border-color: #75b798; --bs-btn-color: #1e6b48;">Empezar Asistente</a>
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
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
    }
</style>