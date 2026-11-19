<?php
// Capítulo 1: Identidad, Raza, Subraza, Clase, Subclase, Trasfondo y Alineamiento
?>

<div class="card shadow-sm border-0">
    <div class="card-body p-4">
        <p class="text-muted small mb-4">Selecciona los rasgos principales de tu aventurero. Al avanzar, estos datos
            configurarán automáticamente tus atributos y estadísticas de combate.</p>

        <!-- El formulario apunta al capítulo 2 usando tu estructura de index.php -->
        <form class="formulario-personaje" method="post" action="index.php?page=personajes/hoja&capitulo=2">

            <h5 class="text-uppercase text-secondary fw-bold fs-6 mb-3 border-bottom pb-2">Información Básica</h5>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="nombre" class="form-label">Nombre del personaje</label>
                    <input id="nombre" name="nombre" type="text" class="form-control" maxlength="80"
                        value="<?php echo htmlspecialchars($personaje['nombre'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                        required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="jugador" class="form-label">Jugador</label>
                    <input id="jugador" name="jugador" type="text" class="form-control" maxlength="80"
                        value="<?php echo htmlspecialchars($personaje['jugador'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                </div>
            </div>

            <!-- SECCIÓN DE 2 COLUMNAS: CLASE Y SUBCLASE -->
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="clase" class="form-label fw-bold">Clase</label>
                    <!-- Al cambiar la clase, recargamos en el capitulo=1 de tu index.php -->
                    <select id="clase" name="clase" class="form-select"
                        onchange="this.form.action='index.php?page=personajes/hoja&capitulo=1'; this.form.submit();"
                        required>
                        <option value="">Seleccionar clase...</option>
                        <?php foreach ($clases as $clase) { ?>
                            <option value="<?php echo htmlspecialchars($clase, ENT_QUOTES, 'UTF-8'); ?>" <?php echo (isset($personaje['clase']) && $personaje['clase'] === $clase) ? ' selected' : ''; ?>>
                                <?php echo htmlspecialchars($clase, ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="subclase" class="form-label fw-bold">Subclase / Arquetipo</label>
                    <select id="subclase" name="subclase" class="form-select">
                        <option value="">Seleccionar subclase...</option>
                        <?php
                        $subclases_disponibles = isset($personaje['clase']) && $personaje['clase'] !== ''
                            ? personaje_opciones_subclases($personaje['clase'])
                            : [];

                        foreach ($subclases_disponibles as $sub) {
                            $seleccionada = (isset($personaje['subclase']) && $personaje['subclase'] === $sub) ? ' selected' : '';
                            ?>
                            <option value="<?php echo htmlspecialchars($sub, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $seleccionada; ?>>
                                <?php echo htmlspecialchars($sub, ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
            </div>

            <!-- SECCIÓN DE 3 COLUMNAS: RAZA, SUBRAZA Y TRASFONDO -->
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="raza" class="form-label fw-bold">Raza</label>
                    <!-- Al cambiar la raza, recargamos en capitulo=1 para actualizar las subrazas -->
                    <select id="raza" name="raza" class="form-select"
                        onchange="this.form.action='index.php?page=personajes/hoja&capitulo=1'; this.form.submit();"
                        required>
                        <option value="">Seleccionar raza...</option>
                        <?php foreach ($razas as $raza) { ?>
                            <option value="<?php echo htmlspecialchars($raza, ENT_QUOTES, 'UTF-8'); ?>" <?php echo (isset($personaje['raza']) && $personaje['raza'] === $raza) ? ' selected' : ''; ?>>
                                <?php echo htmlspecialchars($raza, ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div class="col-md-4 mb-3">
                    <label for="subraza" class="form-label fw-bold">Subraza</label>
                    <select id="subraza" name="subraza" class="form-select">
                        <option value="">Seleccionar subraza...</option>
                        <?php
                        $subrazas_disponibles = (isset($personaje['raza']) && $personaje['raza'] !== '' && function_exists('personaje_opciones_subrazas'))
                            ? personaje_opciones_subrazas($personaje['raza'])
                            : [];

                        foreach ($subrazas_disponibles as $subraza) {
                            $seleccionada = (isset($personaje['subraza']) && $personaje['subraza'] === $subraza) ? ' selected' : '';
                            ?>
                            <option value="<?php echo htmlspecialchars($subraza, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $seleccionada; ?>>
                                <?php echo htmlspecialchars($subraza, ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div class="col-md-4 mb-3">
                    <label for="trasfondo" class="form-label fw-bold">Trasfondo</label>
                    <select id="trasfondo" name="trasfondo" class="form-select">
                        <option value="">Seleccionar trasfondo...</option>
                        <?php foreach ($trasfondos as $trasfondo) { ?>
                            <option value="<?php echo htmlspecialchars($trasfondo, ENT_QUOTES, 'UTF-8'); ?>" <?php echo (isset($personaje['trasfondo']) && $personaje['trasfondo'] === $trasfondo) ? ' selected' : ''; ?>>
                                <?php echo htmlspecialchars($trasfondo, ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
            </div>

            <!-- ALINEAMIENTO, NIVEL Y EXPERIENCIA -->
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="alineamiento" class="form-label">Alineamiento</label>
                    <select id="alineamiento" name="alineamiento" class="form-select">
                        <option value="">Seleccionar alineamiento...</option>
                        <?php
                        $alineamientos = [
                            "Lawful Good" => "Legal Bueno (Lawful Good)",
                            "Lawful Neutral" => "Legal Neutro (Lawful Neutral)",
                            "Lawful Evil" => "Legal Malvado (Lawful Evil)",
                            "Neutral Good" => "Neutral Bueno (Neutral Good)",
                            "Neutral Neutral" => "Neutral Auténtico (Neutral Neutral)",
                            "Neutral Evil" => "Neutral Malvado (Neutral Evil)",
                            "Chaotic Good" => "Caótico Bueno (Chaotic Good)",
                            "Chaotic Neutral" => "Caótico Neutro (Chaotic Neutral)",
                            "Chaotic Evil" => "Caótico Malvado (Chaotic Evil)"
                        ];
                        foreach ($alineamientos as $valor => $etiqueta) {
                            $seleccionado = (isset($personaje['alineamiento']) && $personaje['alineamiento'] === $valor) ? ' selected' : '';
                            echo '<option value="' . $valor . '"' . $seleccionado . '>' . $etiqueta . '</option>';
                        }
                        ?>
                    </select>
                </div>

                <div class="col-md-3 mb-3">
                    <label for="nivel" class="form-label">Nivel</label>
                    <input id="nivel" name="nivel" type="number" class="form-control" min="1" max="20"
                        value="<?php echo (int) ($personaje['nivel'] ?? 1); ?>">
                </div>

                <div class="col-md-3 mb-3">
                    <label for="experiencia" class="form-label">Experiencia</label>
                    <input id="experiencia" name="experiencia" type="number" class="form-control" min="0" max="9999999"
                        value="<?php echo (int) ($personaje['experiencia'] ?? 0); ?>">
                </div>
            </div>

            <!-- Botón de Siguiente Paso -->
            <div class="d-flex justify-content-end mt-4">
                <button class="btn btn-primary px-4 fw-bold" type="submit">
                    Siguiente: Atributos y Combate →
                </button>
            </div>
        </form>
    </div>
</div>