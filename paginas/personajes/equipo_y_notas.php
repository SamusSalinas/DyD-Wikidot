<?php
// Capítulo 4: Equipo, Rasgos y Notas
$personaje['equipo'] = $personaje['equipo'] ?? '';
$personaje['rasgos'] = $personaje['rasgos'] ?? '';
$personaje['equipo_objetos'] = is_array($personaje['equipo_objetos'] ?? null) ? $personaje['equipo_objetos'] : array();

$clase_sel    = $personaje['clase'] ?? '';
$subclase_sel = $personaje['subclase'] ?? '';
$raza_sel     = $personaje['raza'] ?? '';
$subraza_sel  = $personaje['subraza'] ?? '';

$equipo_clase = function_exists('personaje_obtener_equipo_clase')
    ? personaje_obtener_equipo_clase($clase_sel)
    : array();

$todos_objetos_equipo = function_exists('personaje_obtener_todos_objetos_equipo')
    ? personaje_obtener_todos_objetos_equipo()
    : array();

// Mapa slug => cantidad de los objetos que pertenecen a la clase
$cantidades_clase_por_slug = array();
$slugs_clase = array();
foreach ($equipo_clase as $item_eq) {
    if (is_array($item_eq)) {
        $s = trim((string) ($item_eq['slug'] ?? ''));
        $c = max(1, (int) ($item_eq['cantidad'] ?? 1));
        if ($s !== '') {
            $slugs_clase[] = $s;
            $cantidades_clase_por_slug[$s] = $c;
        }
    }
}

// Pre-marcar por defecto los objetos de la clase cuando cambia la clase seleccionada
if (($personaje['_firma_equipo_cap4'] ?? '') !== $clase_sel) {
    $personaje['equipo_objetos'] = array_values(array_unique(array_merge(
        $personaje['equipo_objetos'],
        $slugs_clase
    )));
    $personaje['_firma_equipo_cap4'] = $clase_sel;
}

$traits_raza = function_exists('personaje_obtener_traits_entidad')
    ? personaje_obtener_traits_entidad('raza_trait', 'raza_id', 'razas', 'name', $raza_sel, $raza_sel)
    : array();

$traits_subraza = function_exists('personaje_obtener_traits_entidad')
    ? personaje_obtener_traits_entidad('subraza_trait', 'subraza_id', 'subrazas', 'name', $subraza_sel, $subraza_sel)
    : array();

$todos_traits = array_merge($traits_raza, $traits_subraza);

$spells_disponibles = function_exists('personaje_obtener_spells_clase_subclase')
    ? personaje_obtener_spells_clase_subclase($clase_sel, $subclase_sel)
    : array();
?>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-4">
        
        <form class="formulario-personaje" method="post" action="index.php?page=personajes/guardar_hoja">

            <h5 class="text-uppercase text-secondary fw-bold fs-6 mb-3 border-bottom pb-2">Equipo y Notas del Personaje</h5>
            <p class="text-muted small mb-4">Revisa el equipo de tu clase, selecciona los objetos que llevará tu personaje, consulta sus rasgos y conjuros, y escribe tus notas finales.</p>

            <!-- 1. EQUIPO DE LA CLASE Y CHECKBOXES DE OBJETOS_EQUIPO -->
            <div class="mb-4">
                <h6 class="fw-bold mb-2">
                    Equipo de Clase<?php echo $clase_sel !== '' ? ' (' . htmlspecialchars($clase_sel, ENT_QUOTES, 'UTF-8') . ')' : ''; ?>
                </h6>
                <?php if (!empty($equipo_clase)) { ?>
                    <ul class="list-group list-group-flush border rounded mb-3">
                        <?php foreach ($equipo_clase as $item_eq) { 
                            $nom_eq  = is_array($item_eq) ? ($item_eq['nombre'] ?? '') : (string) $item_eq;
                            $cant_eq = is_array($item_eq) ? (int) ($item_eq['cantidad'] ?? 1) : 1;
                        ?>
                            <li class="list-group-item py-2 small d-flex justify-content-between align-items-center">
                                <span><?php echo htmlspecialchars($nom_eq, ENT_QUOTES, 'UTF-8'); ?></span>
                                <span class="badge bg-secondary">Cantidad: <?php echo $cant_eq; ?></span>
                            </li>
                        <?php } ?>
                    </ul>
                <?php } else { ?>
                    <p class="text-muted small mb-3">No hay equipo inicial registrado en la base de datos para esta clase.</p>
                <?php } ?>

                <label class="form-label fw-bold d-block mb-2">Objetos y Equipo del Personaje</label>
                <?php if (!empty($todos_objetos_equipo)) { ?>
                    <div class="border rounded p-3 bg-light" style="max-height: 280px; overflow-y: auto;">
                        <div class="row row-cols-1 row-cols-md-2 g-2">
                            <?php foreach ($todos_objetos_equipo as $slug_obj => $nombre_obj) {
                                $marcado = in_array($slug_obj, $personaje['equipo_objetos'], true);
                                $cant_de_clase = $cantidades_clase_por_slug[$slug_obj] ?? null;
                                $id_chk = 'obj-eq-' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $slug_obj);
                            ?>
                                <div class="col">
                                    <div class="form-check bg-white border rounded px-3 py-2 h-100 d-flex align-items-center justify-content-between">
                                        <div>
                                            <input class="form-check-input ms-0 me-2"
                                                   type="checkbox"
                                                   id="<?php echo htmlspecialchars($id_chk, ENT_QUOTES, 'UTF-8'); ?>"
                                                   name="equipo_objetos[]"
                                                   value="<?php echo htmlspecialchars($slug_obj, ENT_QUOTES, 'UTF-8'); ?>"
                                                   <?php echo $marcado ? 'checked' : ''; ?>>
                                            <label class="form-check-label small" for="<?php echo htmlspecialchars($id_chk, ENT_QUOTES, 'UTF-8'); ?>">
                                                <?php echo htmlspecialchars($nombre_obj, ENT_QUOTES, 'UTF-8'); ?>
                                            </label>
                                        </div>
                                        <?php if ($cant_de_clase !== null) { ?>
                                            <span class="badge bg-primary ms-2">Clase x<?php echo (int) $cant_de_clase; ?></span>
                                        <?php } ?>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="form-text">Por defecto vienen marcados y activados los objetos incluidos en tu clase.</div>
                <?php } else { ?>
                    <p class="text-muted small">No se encontraron objetos en la tabla <code>objetos_equipo</code>.</p>
                <?php } ?>
            </div>

            <!-- 2. TRAITS DE RAZA Y SUBRAZA -->
            <div class="mb-4">
                <h6 class="fw-bold mb-2">Traits de Raza y Subraza</h6>
                <?php if (!empty($todos_traits)) { ?>
                    <div class="list-group mb-2">
                        <?php foreach ($todos_traits as $tr) { ?>
                            <div class="list-group-item py-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <strong><?php echo htmlspecialchars($tr['nombre'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                    <span class="badge bg-light text-dark border"><?php echo htmlspecialchars($tr['origen'], ENT_QUOTES, 'UTF-8'); ?></span>
                                </div>
                                <?php if (!empty($tr['descripcion'])) { ?>
                                    <p class="text-muted small mb-0 mt-1"><?php echo htmlspecialchars($tr['descripcion'], ENT_QUOTES, 'UTF-8'); ?></p>
                                <?php } ?>
                            </div>
                        <?php } ?>
                    </div>
                <?php } else { ?>
                    <p class="text-muted small mb-2">No se encontraron rasgos asociados a la raza o subraza seleccionada.</p>
                <?php } ?>
            </div>

            <!-- 3. SPELLS DE CLASE Y SUBCLASE -->
            <div class="mb-4">
                <h6 class="fw-bold mb-2">Spells de Clase / Subclase</h6>
                <?php if (!empty($spells_disponibles)) { ?>
                    <div class="table-responsive border rounded">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">Conjuro</th>
                                    <th scope="col" style="width: 15%;">Nivel</th>
                                    <th scope="col" style="width: 25%;">Origen</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($spells_disponibles as $sp) { ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($sp['nombre'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td>
                                            <?php
                                            if ($sp['nivel'] === null) {
                                                echo '<span class="text-muted small">-</span>';
                                            } elseif ((int) $sp['nivel'] === 0) {
                                                echo '<span class="badge bg-secondary">Cantrips</span>';
                                            } else {
                                                echo '<span class="badge bg-primary">Nivel ' . (int) $sp['nivel'] . '</span>';
                                            }
                                            ?>
                                        </td>
                                        <td><span class="text-muted small"><?php echo htmlspecialchars($sp['origen'], ENT_QUOTES, 'UTF-8'); ?></span></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                <?php } else { ?>
                    <p class="text-muted small mb-2">No hay conjuros asociados a la clase o subclase seleccionada.</p>
                <?php } ?>
            </div>

            <!-- 4. CUADRO DE TEXTO PARA NOTAS Y RASGOS -->
            <div class="mb-4">
                <label for="rasgos" class="form-label fw-bold">Notas, Rasgos Adicionales y Detalles de Rol</label>
                <textarea id="rasgos" name="rasgos" class="form-control" rows="5" maxlength="2000" placeholder="Escribe aquí tus notas, trasfondo personal, ideales, vínculos o defectos..."><?php echo htmlspecialchars($personaje['rasgos'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                <div class="form-text">Máximo 2000 caracteres.</div>
            </div>

            <!-- Botones de Navegación -->
            <div class="d-flex justify-content-between mt-4">
                <a href="index.php?page=personajes/hoja&capitulo=3" class="btn btn-outline-secondary px-4 fw-bold">
                    ← Volver: Habilidades
                </a>
                <button class="btn btn-success px-4 fw-bold" type="submit">
                    Guardar y Finalizar Personaje
                </button>
            </div>

        </form>
    </div>
</div>