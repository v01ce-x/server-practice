<?php

require_once __DIR__ . '/_helpers.php';
?>
<div class="telephony-page">
    <?php telephony_page_header('Обзор сети', 'Краткая сводка по абонентам, номерам и рабочим операциям.', $query ?? ''); ?>

    <section class="metrics-grid">
        <?php foreach (($pageStats ?? []) as $item): ?>
            <article class="metric-card">
                <div class="metric-card__value"><?= e($item['value']) ?></div>
                <div class="metric-card__label"><?= e($item['label']) ?></div>
            </article>
        <?php endforeach; ?>
    </section>

    <section class="two-column">
        <article class="panel">
            <h2>Быстрые действия</h2>
            <p class="section-caption">Частые сценарии для системного администратора.</p>
            <div class="toolbar" style="margin-top: 20px;">
                <?php foreach (($quickActions ?? []) as $action): ?>
                    <a class="<?= !empty($action['primary']) ? 'button' : 'button-secondary' ?>"
                       href="<?= e($action['url']) ?>"><?= e($action['label']) ?></a>
                <?php endforeach; ?>
            </div>
            <p class="section-caption" style="margin-top: 16px;"><?= e($lastOperation ?? '') ?></p>
        </article>

        <article class="panel">
            <h2>Нагрузка по подразделениям</h2>
            <p class="section-caption">Текущее распределение занятых номеров.</p>
            <div class="progress-list">
                <?php foreach (($departmentLoad ?? []) as $item): ?>
                    <div class="progress-list__row">
                        <div class="progress-list__top">
                            <span><?= e($item['name']) ?></span>
                            <span><?= e($item['percent']) ?>%</span>
                        </div>
                        <div class="progress-list__track">
                            <div class="progress-list__fill" style="width: <?= e((string)$item['percent']) ?>%;"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </article>
    </section>
</div>
