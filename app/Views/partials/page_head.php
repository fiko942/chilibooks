<section class="page-head">
    <div>
        <p class="eyebrow"><?= esc($eyebrow ?? '') ?></p>
        <h1><?= esc($heading ?? '') ?></h1>
        <p><?= esc($copy ?? '') ?></p>
    </div>
    <?php if (! empty($actions)): ?><div class="toolbar"><?= $actions ?></div><?php endif ?>
</section>
