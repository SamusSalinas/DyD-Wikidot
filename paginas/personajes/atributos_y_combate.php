<?php
// Capítulo 2: Atributos y Combate
$atributos = personaje_atributos();

$personaje['atributos_base'] = $personaje['atributos_base'] ?? ['str' => 10, 'dex' => 10, 'con' => 10, 'int' => 10, 'wis' => 10, 'cha' => 10];
$personaje['atributos'] = $personaje['atributos'] ?? ['str' => 10, 'dex' => 10, 'con' => 10, 'int' => 10, 'wis' => 10, 'cha' => 10];
$personaje['salvaciones'] = $personaje['salvaciones'] ?? [];
$personaje['clase_armadura'] = $personaje['clase_armadura'] ?? 10;
$personaje['velocidad'] = $personaje['velocidad'] ?? 30;
$personaje['puntos_golpe_actuales'] = $personaje['puntos_golpe_actuales'] ?? 10;
$personaje['puntos_golpe_maximos'] = $personaje['puntos_golpe_maximos'] ?? 10;
$personaje['puntos_golpe_temporales'] = $personaje['puntos_golpe_temporales'] ?? 0;

$info_bonos_cap2 = function_exists('personaje_aplicar_bonos_capitulo_2')
    ? personaje_aplicar_bonos_capitulo_2($personaje)
    : ['atributos' => ['str' => 0, 'dex' => 0, 'con' => 0, 'int' => 0, 'wis' => 0, 'cha' => 0], 'detalle' => [], 'dado_golpe' => 8, 'velocidad' => $personaje['velocidad']];

$bonos_por_attr = $info_bonos_cap2['atributos'] ?? [];
$nivel_actual = (int) ($personaje['nivel'] ?? 1);
$bono_comp = personaje_bono_competencia($nivel_actual);
?>

<style>
    .dados-4d6 {
        display: flex;
        gap: 6px;
        margin-top: 6px;
    }
    .dado-cuadrito {
        width: 28px;
        height: 28px;
        border-radius: 5px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.82rem;
        border: 1px solid #d0d5dd;
        background-color: #f8f9fa;
        color: #6c757d;
        transition: all 0.15s ease;
    }
    .dado-cuadrito.dado-usado {
        background-color: #1d8cf8;
        border-color: #1d8cf8;
        color: #ffffff;
    }
    .dado-cuadrito.dado-descartado {
        background-color: #6c757d;
        border-color: #6c757d;
        color: #ffffff;
        opacity: 0.85;
    }
</style>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-4">
        
        <form class="formulario-personaje" method="post" action="index.php?page=personajes/hoja&capitulo=3">

            <h5 class="text-uppercase text-secondary fw-bold fs-6 mb-3 border-bottom pb-2">Atributos y Tiradas de Salvación</h5>
            <p class="text-muted small mb-3">Introduce una puntuación base entre 1 y 30 o usa <strong>Roll</strong>.</p>

            <?php if (!empty($info_bonos_cap2['detalle'])) { ?>
                <div class="alert alert-success py-2 px-3 small mb-3">
                    <strong>Bonos aplicados:</strong>
                    <?php echo htmlspecialchars(implode(' · ', $info_bonos_cap2['detalle']), ENT_QUOTES, 'UTF-8'); ?>
                    <span class="ms-2 text-muted">(Dado de golpe: d<?php echo (int) $info_bonos_cap2['dado_golpe']; ?> · Velocidad: <?php echo (int) $info_bonos_cap2['velocidad']; ?> pies)</span>
                </div>
            <?php } ?>
            
            <div class="table-responsive mb-4">
                <table class="table table-hover align-middle" id="tabla-atributos-cap2" data-bono-comp="<?php echo (int) $bono_comp; ?>">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Atributo</th>
                            <th scope="col">Puntuación base</th>
                            <th scope="col">Bono</th>
                            <th scope="col">Total</th>
                            <th scope="col">Modificador</th>
                            <th scope="col">Salvación competente</th>
                            <th scope="col">Mod. de salvación</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($atributos as $codigo => $atributo) { 
                            $campo_bd = $atributo['campo'];
                            $bono_attr = (int) ($bonos_por_attr[$codigo] ?? 0);
                            $base_actual = (int) ($personaje['atributos_base'][$codigo] ?? max(1, (($personaje['atributos'][$codigo] ?? 10) - $bono_attr)));
                            $total_actual = max(1, min(30, $base_actual + $bono_attr));
                            $es_salv_comp = isset($personaje['salvaciones']) && in_array($codigo, $personaje['salvaciones'], true);
                            $mod_base = personaje_modificador($total_actual);
                            $mod_salv = $mod_base + ($es_salv_comp ? $bono_comp : 0);
                        ?>
                            <tr class="fila-atributo" data-codigo="<?php echo htmlspecialchars($codigo, ENT_QUOTES, 'UTF-8'); ?>" data-bono="<?php echo $bono_attr; ?>">
                                <th scope="row">
                                    <?php echo htmlspecialchars($atributo['nombre'], ENT_QUOTES, 'UTF-8'); ?> (<?php echo strtoupper($codigo); ?>)
                                </th>
                                <td style="min-width: 175px;">
                                    <div class="d-flex align-items-center gap-2">
                                        <input id="<?php echo htmlspecialchars($campo_bd, ENT_QUOTES, 'UTF-8'); ?>_base" 
                                               name="atributos_base[<?php echo htmlspecialchars($codigo, ENT_QUOTES, 'UTF-8'); ?>]" 
                                               type="number" class="form-control form-control-sm input-base-attr" style="width: 72px;" 
                                               min="1" max="30" value="<?php echo $base_actual; ?>">
                                        <button type="button" class="btn btn-sm btn-outline-primary btn-roll-4d6">Roll</button>
                                    </div>
                                    <div class="dados-4d6" aria-label="Dados 4d6 para <?php echo htmlspecialchars($atributo['nombre'], ENT_QUOTES, 'UTF-8'); ?>">
                                        <span class="dado-cuadrito"></span>
                                        <span class="dado-cuadrito"></span>
                                        <span class="dado-cuadrito"></span>
                                        <span class="dado-cuadrito"></span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge <?php echo $bono_attr > 0 ? 'bg-info text-dark' : 'bg-light text-muted border'; ?>">
                                        <?php echo personaje_formatear_modificador($bono_attr); ?>
                                    </span>
                                </td>
                                <td>
                                    <strong class="valor-total-attr fs-6"><?php echo $total_actual; ?></strong>
                                    <!-- Este input envía el total final con el nombre exacto que espera tu sistema -->
                                    <input type="hidden" 
                                           id="<?php echo htmlspecialchars($campo_bd, ENT_QUOTES, 'UTF-8'); ?>" 
                                           name="<?php echo htmlspecialchars($campo_bd, ENT_QUOTES, 'UTF-8'); ?>" 
                                           class="input-total-attr" 
                                           value="<?php echo $total_actual; ?>">
                                </td>
                                <td>
                                    <span class="badge bg-secondary badge-mod-attr">
                                        <?php echo personaje_formatear_modificador($mod_base); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="form-check">
                                        <input class="form-check-input check-salvacion-attr" 
                                               id="salvacion-<?php echo htmlspecialchars($codigo, ENT_QUOTES, 'UTF-8'); ?>" 
                                               type="checkbox" name="salvaciones[]" value="<?php echo htmlspecialchars($codigo, ENT_QUOTES, 'UTF-8'); ?>"
                                               <?php echo $es_salv_comp ? ' checked' : ''; ?>>
                                        <label class="form-check-label small" for="salvacion-<?php echo htmlspecialchars($codigo, ENT_QUOTES, 'UTF-8'); ?>">Competente</label>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-semibold valor-mod-salv">
                                        <?php echo personaje_formatear_modificador($mod_salv); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
            
            <p class="text-muted small mb-4"><strong>Bono de competencia actual:</strong> <?php echo personaje_formatear_modificador($bono_comp); ?></p>


            <!-- SECCIÓN DE COMBATE -->
            <h5 class="text-uppercase text-secondary fw-bold fs-6 mb-3 border-bottom pb-2">Estadísticas de Combate</h5>
            
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="clase_armadura" class="form-label">Clase de Armadura (CA)</label>
                    <input id="clase_armadura" name="clase_armadura" type="number" class="form-control" min="0" max="99" value="<?php echo (int) $personaje['clase_armadura']; ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label for="iniciativa" class="form-label">Iniciativa (Mod. Destreza)</label>
                    <input id="iniciativa" type="text" class="form-control bg-light" value="<?php echo htmlspecialchars(personaje_formatear_modificador(personaje_modificador($personaje['atributos']['dex'] ?? 10)), ENT_QUOTES, 'UTF-8'); ?>" readonly>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="velocidad" class="form-label">Velocidad (pies)</label>
                    <input id="velocidad" name="velocidad" type="number" class="form-control" min="0" max="999" value="<?php echo (int) $personaje['velocidad']; ?>">
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="puntos_golpe_actuales" class="form-label">Puntos de Golpe Actuales</label>
                    <input id="puntos_golpe_actuales" name="puntos_golpe_actuales" type="number" class="form-control" min="0" max="9999" value="<?php echo (int) $personaje['puntos_golpe_actuales']; ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label for="puntos_golpe_maximos" class="form-label">Puntos de Golpe Máximos</label>
                    <input id="puntos_golpe_maximos" name="puntos_golpe_maximos" type="number" class="form-control" min="0" max="9999" value="<?php echo (int) $personaje['puntos_golpe_maximos']; ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label for="puntos_golpe_temporales" class="form-label">Puntos de Golpe Temporales</label>
                    <input id="puntos_golpe_temporales" name="puntos_golpe_temporales" type="number" class="form-control" min="0" max="9999" value="<?php echo (int) $personaje['puntos_golpe_temporales']; ?>">
                </div>
            </div>

            <!-- Botones de Navegación -->
            <div class="d-flex justify-content-between mt-4">
                <a href="index.php?page=personajes/hoja&capitulo=1" class="btn btn-outline-secondary px-4 fw-bold">
                    ← Volver: Identidad
                </a>
                <button class="btn btn-primary px-4 fw-bold" type="submit">
                    Siguiente: Habilidades →
                </button>
            </div>

        </form>
    </div>
</div>

<script>
(function () {
    const tabla = document.getElementById('tabla-atributos-cap2');
    if (!tabla) return;

    const bonoComp = parseInt(tabla.dataset.bonoComp || '2', 10);

    function calcularModificador(puntaje) {
        return Math.floor((puntaje - 10) / 2);
    }

    function formatearMod(mod) {
        return (mod >= 0 ? '+' : '') + mod;
    }

    function actualizarFila(fila, nuevoBase) {
        const bonoAttr = parseInt(fila.dataset.bono || '0', 10);
        const inputBase = fila.querySelector('.input-base-attr');
        const inputTotal = fila.querySelector('.input-total-attr');
        const textoTotal = fila.querySelector('.valor-total-attr');
        const badgeMod = fila.querySelector('.badge-mod-attr');
        const checkSalv = fila.querySelector('.check-salvacion-attr');
        const textoSalv = fila.querySelector('.valor-mod-salv');

        let base = typeof nuevoBase === 'number' ? nuevoBase : parseInt(inputBase.value || '1', 10);
        if (Number.isNaN(base)) base = 1;
        base = Math.max(1, Math.min(30, base));
        if (typeof nuevoBase === 'number') {
            inputBase.value = base;
        }

        const total = Math.max(1, Math.min(30, base + bonoAttr));
        if (inputTotal) inputTotal.value = total;
        if (textoTotal) textoTotal.textContent = total;

        const mod = calcularModificador(total);
        if (badgeMod) badgeMod.textContent = formatearMod(mod);

        const modSalv = mod + (checkSalv && checkSalv.checked ? bonoComp : 0);
        if (textoSalv) textoSalv.textContent = formatearMod(modSalv);

        // Si es Destreza, actualizar también Iniciativa si existe en el DOM
        if (fila.dataset.codigo === 'dex') {
            const inputIni = document.getElementById('iniciativa');
            if (inputIni) inputIni.value = formatearMod(mod);
        }
    }

    tabla.querySelectorAll('.fila-atributo').forEach((fila) => {
        const btnRoll = fila.querySelector('.btn-roll-4d6');
        const inputBase = fila.querySelector('.input-base-attr');
        const checkSalv = fila.querySelector('.check-salvacion-attr');
        const cuadritos = Array.from(fila.querySelectorAll('.dado-cuadrito'));

        if (btnRoll) {
            btnRoll.addEventListener('click', () => {
                const dados = [
                    Math.floor(Math.random() * 6) + 1,
                    Math.floor(Math.random() * 6) + 1,
                    Math.floor(Math.random() * 6) + 1,
                    Math.floor(Math.random() * 6) + 1,
                ].sort((a, b) => b - a);

                const sumaTresMayores = dados[0] + dados[1] + dados[2];

                cuadritos.forEach((cuadro, idx) => {
                    cuadro.textContent = dados[idx];
                    cuadro.classList.remove('dado-usado', 'dado-descartado');
                    if (idx < 3) {
                        cuadro.classList.add('dado-usado');
                    } else {
                        cuadro.classList.add('dado-descartado');
                    }
                });

                actualizarFila(fila, sumaTresMayores);
            });
        }

        if (inputBase) {
            inputBase.addEventListener('input', () => actualizarFila(fila));
        }

        if (checkSalv) {
            checkSalv.addEventListener('change', () => actualizarFila(fila));
        }
    });
})();
</script>