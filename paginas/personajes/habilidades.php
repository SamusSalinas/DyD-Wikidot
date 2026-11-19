<?php
// Capítulo 3: Habilidades
// Cargamos las habilidades y atributos oficiales desde tus funciones del sistema
$habilidades = function_exists('personaje_habilidades') ? personaje_habilidades() : array();
$atributos   = function_exists('personaje_atributos') ? personaje_atributos() : array();

// Asegurar que el array de habilidades del personaje exista en sesión
$personaje['habilidades'] = $personaje['habilidades'] ?? array();

$info_prof_cap3 = function_exists('personaje_aplicar_proficiencias_capitulo_3')
    ? personaje_aplicar_proficiencias_capitulo_3($personaje)
    : array(
        'proficiencias'           => array(),
        'proficiencias_trasfondo' => array(),
        'proficiencias_clase'     => array(),
        'proficiencias_subclase'  => array(),
        'habilidades'             => array(),
    );

$prof_trasfondo = $info_prof_cap3['proficiencias_trasfondo'] ?? array();
$prof_clase     = $info_prof_cap3['proficiencias_clase'] ?? array();
$prof_subclase  = $info_prof_cap3['proficiencias_subclase'] ?? array();
?>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-4">
        
        <!-- El formulario apunta al Capítulo 4 (Equipo y Notas) -->
        <form class="formulario-personaje" method="post" action="index.php?page=personajes/hoja&capitulo=4">

            <h5 class="text-uppercase text-secondary fw-bold fs-6 mb-3 border-bottom pb-2">Habilidades del Personaje</h5>
            <p class="text-muted small mb-3">Selecciona las habilidades en las que tu aventurero es competente. Los modificadores se calculan automáticamente según el atributo asociado y tu bono de competencia.</p>

            <?php if (!empty($prof_trasfondo) || !empty($prof_clase) || !empty($prof_subclase)) { ?>
                <div class="alert alert-info py-2 px-3 small mb-3">
                    <?php if (!empty($prof_trasfondo)) { ?>
                        <div>
                            <strong>Proficiencias del trasfondo:</strong>
                            <?php echo htmlspecialchars(implode(' · ', $prof_trasfondo), ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                    <?php } ?>
                    <?php if (!empty($prof_clase)) { ?>
                        <div class="<?php echo !empty($prof_trasfondo) ? 'mt-1' : ''; ?>">
                            <strong>Proficiencias de la clase:</strong>
                            <?php echo htmlspecialchars(implode(' · ', $prof_clase), ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                    <?php } ?>
                    <?php if (!empty($prof_subclase)) { ?>
                        <div class="<?php echo (!empty($prof_trasfondo) || !empty($prof_clase)) ? 'mt-1' : ''; ?>">
                            <strong>Proficiencias de la subclase:</strong>
                            <?php echo htmlspecialchars(implode(' · ', $prof_subclase), ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>

            <div class="table-responsive mb-4">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" style="width: 10%;">Competente</th>
                            <th scope="col" style="width: 45%;">Habilidad</th>
                            <th scope="col" style="width: 25%;">Atributo</th>
                            <th scope="col" style="width: 20%;">Modificador</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($habilidades as $clave => $habilidad) { 
                            // Verificamos si la habilidad está seleccionada en la sesión de forma segura
                            $marcado = (isset($personaje['habilidades']) && in_array($clave, $personaje['habilidades'], true)) ? ' checked' : '';
                            
                            // Obtenemos el nombre del atributo asociado (ej: Fuerza, Destreza, etc.)
                            $nombre_atributo = $atributos[$habilidad['atributo']]['nombre'] ?? $habilidad['atributo'];
                        ?>
                            <tr>
                                <td>
                                    <div class="form-check">
                                        <input class="form-check-input" id="habilidad-<?php echo htmlspecialchars($clave, ENT_QUOTES, 'UTF-8'); ?>" 
                                               type="checkbox" name="habilidades[]" value="<?php echo htmlspecialchars($clave, ENT_QUOTES, 'UTF-8'); ?>"<?php echo $marcado; ?>>
                                    </div>
                                </td>
                                <th scope="row">
                                    <label class="fw-normal" for="habilidad-<?php echo htmlspecialchars($clave, ENT_QUOTES, 'UTF-8'); ?>">
                                        <?php echo htmlspecialchars($habilidad['nombre'], ENT_QUOTES, 'UTF-8'); ?>
                                    </label>
                                </th>
                                <td>
                                    <span class="text-muted small"><?php echo htmlspecialchars($nombre_atributo, ENT_QUOTES, 'UTF-8'); ?></span>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">
                                        <?php echo personaje_formatear_modificador(personaje_modificador_habilidad($clave, $personaje)); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <!-- Botones de Navegación -->
            <div class="d-flex justify-content-between mt-4">
                <a href="index.php?page=personajes/hoja&capitulo=2" class="btn btn-outline-secondary px-4 fw-bold">
                    ← Volver: Atributos y Combate
                </a>
                <button class="btn btn-primary px-4 fw-bold" type="submit">
                    Siguiente: Equipo y Notas →
                </button>
            </div>

        </form>
    </div>
</div>