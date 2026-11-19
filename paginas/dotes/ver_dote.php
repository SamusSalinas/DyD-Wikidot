<?php
// Control de sesión y conexión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../includes/conexion.php';

// Tomamos el ID del dote desde la URL (por defecto el 1 si no viene definido)
$id = isset($_GET['id']) ? (int)$_GET['id'] : 1;

$sql = "SELECT * FROM dotes WHERE id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();
?>

<div class="container py-4">
    <!-- Barra superior de navegación / volver -->
    <div class="mb-4">
        <a href="index.php?page=dotes/inicio" class="btn btn-outline-wiki btn-sm fw-bold">
            <i class="bi bi-arrow-left"></i> Volver al listado de dotes
        </a>
    </div>

    <?php if ($resultado && $resultado->num_rows > 0): 
        $dote = $resultado->fetch_assoc();
        // Separamos los beneficios por coma para armar los ítems
        $lista_beneficios = !empty($dote['beneficios']) ? explode(',', $dote['beneficios']) : array();
    ?>

        <!-- Tarjeta principal de la Dote -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body p-4 p-md-5">
                <div class="row align-items-center mb-4">
                    <div class="col">
                        <span class="badge mb-2" style="background-color: #19352b; color: #fff;">Dote de 5ª Edición</span>
                        <h1 class="fw-bold mb-1" style="color: #19352b;"><?php echo htmlspecialchars($dote['nombre'], ENT_QUOTES, 'UTF-8'); ?></h1>
                    </div>
                </div>

                <!-- Prerrequisito destacado -->
                <div class="p-3 mb-4 rounded bg-light border-start border-4" style="border-color: #19352b !important;">
                    <span class="d-block text-uppercase small text-muted fw-bold">Prerrequisito</span>
                    <span class="text-dark fw-semibold">
                        <?php 
                        $prerequisito = trim($dote['prerequisito'] ?? '');
                        echo !empty($prerequisito) ? htmlspecialchars($prerequisito, ENT_QUOTES, 'UTF-8') : 'Ninguno'; 
                        ?>
                    </span>
                </div>

                <!-- Beneficios -->
                <h3 class="fw-bold fs-5 mb-3" style="color: #19352b;">Beneficios</h3>
                <?php if (!empty($lista_beneficios)): ?>
                    <div class="list-group list-group-flush border rounded overflow-hidden">
                        <?php foreach ($lista_beneficios as $beneficio): 
                            $beneficio_limpio = trim(rtrim($beneficio, '.'));
                            if (!empty($beneficio_limpio)):
                        ?>
                            <div class="list-group-item bg-light-subtle py-3 px-3 d-flex align-items-start">
                                <span class="me-3" style="color: #19352b;"></span>
                                <span class="text-secondary"><?php echo htmlspecialchars($beneficio_limpio, ENT_QUOTES, 'UTF-8'); ?>.</span>
                            </div>
                        <?php 
                            endif;
                        endforeach; 
                        ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No hay beneficios detallados para esta dote.</p>
                <?php endif; ?>
            </div>
        </div>

    <?php else: ?>
        <!-- Mensaje de error si la dote no existe -->
        <div class="card shadow-sm border-0 text-center py-5">
            <div class="card-body">
                <span class="display-4 text-muted">🔍</span>
                <h4 class="fw-bold mt-3 text-secondary">Dote no encontrada</h4>
                <p class="text-muted mb-4">No existe un dote con ese identificador en la base de datos.</p>
                <a href="index.php?page=dotes/inicio" class="btn btn-wiki fw-bold">Volver a todos los dotes</a>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
/* Estilos corporativos alineados al verde de la Wiki */
.btn-outline-wiki {
    color: #19352b;
    border-color: #19352b;
}
.btn-outline-wiki:hover {
    background-color: #19352b;
    color: #ffffff;
    border-color: #19352b;
}
.btn-wiki {
    background-color: #19352b;
    color: #ffffff;
    border-color: #19352b;
}
.btn-wiki:hover {
    background-color: #122720;
    color: #ffffff;
}
</style>

<?php
$stmt->close();
?>