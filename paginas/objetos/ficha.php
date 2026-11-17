<div class="objeto-ficha">
    <dl class="objeto-ficha-metadatos">
        <div>
            <dt>Categoría</dt>
            <dd><?php echo htmlspecialchars($objeto['categoria'], ENT_QUOTES, 'UTF-8'); ?></dd>
        </div>
        <div>
            <dt>Coste</dt>
            <dd><?php echo htmlspecialchars($objeto['coste'], ENT_QUOTES, 'UTF-8'); ?></dd>
        </div>
        <?php if (!empty($objeto['peso'])) { ?>
            <div>
                <dt>Peso</dt>
                <dd><?php echo htmlspecialchars($objeto['peso'], ENT_QUOTES, 'UTF-8'); ?></dd>
            </div>
        <?php } ?>
        <?php if (!empty($objeto['dano'])) { ?>
            <div>
                <dt>Daño</dt>
                <dd><?php echo htmlspecialchars($objeto['dano'], ENT_QUOTES, 'UTF-8'); ?></dd>
            </div>
        <?php } ?>
        <?php if (!empty($objeto['clase_armadura'])) { ?>
            <div>
                <dt>Clase de Armadura (CA)</dt>
                <dd><?php echo htmlspecialchars($objeto['clase_armadura'], ENT_QUOTES, 'UTF-8'); ?></dd>
            </div>
        <?php } ?>
    </dl>

    <?php if (!empty($objeto['propiedades'])) { ?>
        <h3>Propiedades</h3>
        <ul class="objeto-propiedades">
            <?php foreach (preg_split('/\r\n|\r|\n/', $objeto['propiedades']) as $propiedad) { ?>
                <?php if (trim($propiedad) !== '') { ?>
                    <li><?php echo htmlspecialchars($propiedad, ENT_QUOTES, 'UTF-8'); ?></li>
                <?php } ?>
            <?php } ?>
        </ul>
    <?php } ?>

    <h3>Descripción</h3>
    <?php if (!empty($objeto['descripcion'])) { ?>
        <p><?php echo nl2br(htmlspecialchars($objeto['descripcion'], ENT_QUOTES, 'UTF-8')); ?></p>
    <?php } else { ?>
        <p>No hay una descripción adicional disponible en el catálogo local.</p>
    <?php } ?>
</div>
