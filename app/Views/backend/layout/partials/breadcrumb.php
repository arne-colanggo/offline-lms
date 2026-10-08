<?php
// app/Views/backend/partials/breadcrumb.php
helper('breadcrumb');

// Optional: controllers can pass $breadcrumbLabels for dynamic names
$items = breadcrumb_items($breadcrumbLabels ?? []);
?>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-3">
        <?php foreach ($items as $item): ?>
            <?php if ($item['active']): ?>
                <li class="breadcrumb-item active" aria-current="page">
                    <?= esc($item['label']) ?>
                </li>
            <?php elseif ($item['url']): ?>
                <li class="breadcrumb-item">
                    <a href="<?= esc($item['url'], 'attr') ?>">
                        <?= esc($item['label']) ?>
                    </a>
                </li>
            <?php else: ?>
                <li class="breadcrumb-item">
                    <?= esc($item['label']) ?>
                </li>
            <?php endif; ?>
        <?php endforeach; ?>
    </ol>
</nav>