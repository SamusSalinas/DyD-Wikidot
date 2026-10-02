<?php
require_once __DIR__ . '/../../includes/funciones_personaje.php';

$resultado = personaje_generar_hoja($_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : array());
$personaje = $resultado['datos'];
$errores = $resultado['errores'];
$atributos = personaje_atributos();
$habilidades = personaje_habilidades();
$clases = personaje_opciones_clases();
$razas = personaje_opciones_razas();

function personaje_marcar_seleccionado($seleccionados, $valor)
{
    return in_array($valor, $seleccionados, true) ? ' checked' : '';
}
?>
<h2>Hoja de personaje</h2>
<p>Completa los datos para armar una hoja de personaje. Los campos de combate y competencias pueden editarse según tu personaje.</p>

<?php if (count($errores) > 0) { ?>
    <div role="alert">
        <h3>Revisa estos datos</h3>
        <ul>
            <?php foreach ($errores as $error) { ?>
                <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
            <?php } ?>
        </ul>
    </div>
<?php } ?>

<form method="post" action="index.php?page=personajes/hoja">
    <fieldset>
        <legend>Identidad</legend>
        <p>
            <label for="nombre">Nombre del personaje</label><br>
            <input id="nombre" name="nombre" type="text" maxlength="80" value="<?php echo htmlspecialchars($personaje['nombre'], ENT_QUOTES, 'UTF-8'); ?>">
        </p>
        <p>
            <label for="jugador">Jugador</label><br>
            <input id="jugador" name="jugador" type="text" maxlength="80" value="<?php echo htmlspecialchars($personaje['jugador'], ENT_QUOTES, 'UTF-8'); ?>">
        </p>
        <p>
            <label for="raza">Raza</label><br>
            <select id="raza" name="raza">
                <option value="">Seleccionar</option>
                <?php foreach ($razas as $raza) { ?>
                    <option value="<?php echo htmlspecialchars($raza, ENT_QUOTES, 'UTF-8'); ?>"<?php echo $personaje['raza'] === $raza ? ' selected' : ''; ?>><?php echo htmlspecialchars($raza, ENT_QUOTES, 'UTF-8'); ?></option>
                <?php } ?>
            </select>
        </p>
        <p>
            <label for="clase">Clase</label><br>
            <select id="clase" name="clase">
                <option value="">Seleccionar</option>
                <?php foreach ($clases as $clase) { ?>
                    <option value="<?php echo htmlspecialchars($clase, ENT_QUOTES, 'UTF-8'); ?>"<?php echo $personaje['clase'] === $clase ? ' selected' : ''; ?>><?php echo htmlspecialchars($clase, ENT_QUOTES, 'UTF-8'); ?></option>
                <?php } ?>
            </select>
        </p>
        <p>
            <label for="trasfondo">Trasfondo</label><br>
            <input id="trasfondo" name="trasfondo" type="text" maxlength="80" value="<?php echo htmlspecialchars($personaje['trasfondo'], ENT_QUOTES, 'UTF-8'); ?>">
        </p>
        <p>
            <label for="alineamiento">Alineamiento</label><br>
            <input id="alineamiento" name="alineamiento" type="text" maxlength="40" value="<?php echo htmlspecialchars($personaje['alineamiento'], ENT_QUOTES, 'UTF-8'); ?>">
        </p>
        <p>
            <label for="nivel">Nivel</label>
            <input id="nivel" name="nivel" type="number" min="1" max="20" value="<?php echo (int) $personaje['nivel']; ?>">
            <label for="experiencia">Experiencia</label>
            <input id="experiencia" name="experiencia" type="number" min="0" max="9999999" value="<?php echo (int) $personaje['experiencia']; ?>">
        </p>
    </fieldset>

    <fieldset>
        <legend>Atributos</legend>
        <p>Introduce una puntuación entre 1 y 30. Los modificadores se calculan al generar la hoja.</p>
        <table>
            <thead>
                <tr><th scope="col">Atributo</th><th scope="col">Puntuación</th><th scope="col">Modificador</th><th scope="col">Salvación competente</th><th scope="col">Modificador de salvación</th></tr>
            </thead>
            <tbody>
                <?php foreach ($atributos as $codigo => $atributo) { ?>
                    <tr>
                        <th scope="row"><?php echo htmlspecialchars($atributo['nombre'], ENT_QUOTES, 'UTF-8'); ?> (<?php echo strtoupper($codigo); ?>)</th>
                        <td><label for="<?php echo htmlspecialchars($atributo['campo'], ENT_QUOTES, 'UTF-8'); ?>">Puntuación</label>
                            <input id="<?php echo htmlspecialchars($atributo['campo'], ENT_QUOTES, 'UTF-8'); ?>" name="<?php echo htmlspecialchars($atributo['campo'], ENT_QUOTES, 'UTF-8'); ?>" type="number" min="1" max="30" value="<?php echo (int) $personaje['atributos'][$codigo]; ?>"></td>
                        <td><?php echo personaje_formatear_modificador(personaje_modificador($personaje['atributos'][$codigo])); ?></td>
                        <td><input id="salvacion-<?php echo htmlspecialchars($codigo, ENT_QUOTES, 'UTF-8'); ?>" type="checkbox" name="salvaciones[]" value="<?php echo htmlspecialchars($codigo, ENT_QUOTES, 'UTF-8'); ?>"<?php echo personaje_marcar_seleccionado($personaje['salvaciones'], $codigo); ?>>
                            <label for="salvacion-<?php echo htmlspecialchars($codigo, ENT_QUOTES, 'UTF-8'); ?>">Competente</label></td>
                        <td><?php
                            $modificador_salvacion = personaje_modificador($personaje['atributos'][$codigo]);
                            if (in_array($codigo, $personaje['salvaciones'], true)) {
                                $modificador_salvacion += personaje_bono_competencia($personaje['nivel']);
                            }
                            echo personaje_formatear_modificador($modificador_salvacion);
                        ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        <p>Bono de competencia: <?php echo personaje_formatear_modificador(personaje_bono_competencia($personaje['nivel'])); ?></p>
    </fieldset>

    <fieldset>
        <legend>Combate</legend>
        <p>
            <label for="clase_armadura">Clase de armadura</label>
            <input id="clase_armadura" name="clase_armadura" type="number" min="0" max="99" value="<?php echo (int) $personaje['clase_armadura']; ?>">
        </p>
        <p>
            <label for="iniciativa">Iniciativa (modificador de Destreza)</label>
            <input id="iniciativa" type="text" value="<?php echo htmlspecialchars(personaje_formatear_modificador(personaje_modificador($personaje['atributos']['dex'])), ENT_QUOTES, 'UTF-8'); ?>" readonly>
        </p>
        <p>
            <label for="velocidad">Velocidad</label>
            <input id="velocidad" name="velocidad" type="number" min="0" max="999" value="<?php echo (int) $personaje['velocidad']; ?>">
        </p>
        <p>
            <label for="puntos_golpe_actuales">Puntos de golpe actuales</label>
            <input id="puntos_golpe_actuales" name="puntos_golpe_actuales" type="number" min="0" max="9999" value="<?php echo (int) $personaje['puntos_golpe_actuales']; ?>">
            <label for="puntos_golpe_maximos">Máximo</label>
            <input id="puntos_golpe_maximos" name="puntos_golpe_maximos" type="number" min="0" max="9999" value="<?php echo (int) $personaje['puntos_golpe_maximos']; ?>">
        </p>
        <p>
            <label for="puntos_golpe_temporales">Puntos de golpe temporales</label>
            <input id="puntos_golpe_temporales" name="puntos_golpe_temporales" type="number" min="0" max="9999" value="<?php echo (int) $personaje['puntos_golpe_temporales']; ?>">
        </p>
    </fieldset>

    <fieldset>
        <legend>Habilidades</legend>
        <table>
            <thead><tr><th scope="col">Competente</th><th scope="col">Habilidad</th><th scope="col">Atributo</th><th scope="col">Modificador</th></tr></thead>
            <tbody>
                <?php foreach ($habilidades as $clave => $habilidad) { ?>
                    <tr>
                        <td><input id="habilidad-<?php echo htmlspecialchars($clave, ENT_QUOTES, 'UTF-8'); ?>" type="checkbox" name="habilidades[]" value="<?php echo htmlspecialchars($clave, ENT_QUOTES, 'UTF-8'); ?>"<?php echo personaje_marcar_seleccionado($personaje['habilidades'], $clave); ?>></td>
                        <th scope="row"><label for="habilidad-<?php echo htmlspecialchars($clave, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($habilidad['nombre'], ENT_QUOTES, 'UTF-8'); ?></label></th>
                        <td><?php echo htmlspecialchars($atributos[$habilidad['atributo']]['nombre'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo personaje_formatear_modificador(personaje_modificador_habilidad($clave, $personaje)); ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </fieldset>

    <fieldset>
        <legend>Equipo y notas</legend>
        <p><label for="equipo">Equipo y objetos</label><br>
            <textarea id="equipo" name="equipo" rows="4" cols="50" maxlength="2000"><?php echo htmlspecialchars($personaje['equipo'], ENT_QUOTES, 'UTF-8'); ?></textarea></p>
        <p><label for="rasgos">Rasgos, competencias y notas</label><br>
            <textarea id="rasgos" name="rasgos" rows="4" cols="50" maxlength="2000"><?php echo htmlspecialchars($personaje['rasgos'], ENT_QUOTES, 'UTF-8'); ?></textarea></p>
    </fieldset>

    <p><button type="submit">Generar hoja</button></p>
</form>
