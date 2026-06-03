<section class="page-head">
    <div class="page-head-copy">
        <p class="eyebrow"><?= esc($eyebrow ?? '') ?></p>
        <h1><?= esc($heading ?? '') ?></h1>
        <p><?= esc($copy ?? '') ?></p>
    </div>
<?php if (! empty($controls) || ! empty($actions)): ?>
    <div class="page-head-actions">
        <?php if (! empty($controls)): ?><div class="page-head-controls"><?= $controls ?></div><?php endif ?>
        <?php if (! empty($actions)): ?><div class="toolbar page-head-toolbar"><?= $actions ?></div><?php endif ?>
    </div>
<?php endif ?>
</section>
