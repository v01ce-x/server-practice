<?php

require_once __DIR__ . '/_helpers.php';
?>
<div class="telephony-page">
    <?php telephony_page_header('Отчёты', 'Количество абонентов по подразделениям и помещениям.', $query ?? ''); ?>

    <div class="toolbar toolbar--end">
        <a class="button-secondary" href="<?= e(url('/reports/export')) ?>">Экспорт CSV</a>
    </div>

    <section class="metrics-grid metrics-grid--reports">
        <?php foreach (($summaryStats ?? []) as $item): ?>
            <article class="metric-card">
                <div class="metric-card__value"><?= e($item['value']) ?></div>
                <div class="metric-card__label"><?= e($item['label']) ?></div>
            </article>
        <?php endforeach; ?>
    </section>

    <section class="two-column">
        <article class="panel">
            <h2>Абоненты по подразделениям</h2>
            <p class="section-caption">Простая столбиковая сводка.</p>
            <div class="progress-list">
                <?php foreach (($departmentStats ?? []) as $item): ?>
                    <div class="progress-list__row">
                        <div class="progress-list__top">
                            <span><?= e($item['name']) ?></span>
                            <span><?= e($item['count']) ?></span>
                        </div>
                        <div class="progress-list__track">
                            <div class="progress-list__fill" style="width: <?= e((string)$item['percent']) ?>%;"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </article>

        <article class="panel">
            <h2>Абоненты по помещениям</h2>
            <p class="section-caption">Топ помещений по числу закреплённых абонентов.</p>
            <div class="compact-list">
                <?php foreach (($roomStats ?? []) as $item): ?>
                    <div class="compact-item">
                        <div class="compact-item__title"><?= e($item['name']) ?></div>
                        <div class="compact-item__meta">
                            <?= e($item['count']) ?> <?= $item['count'] === 1 ? 'абонент' : ($item['count'] < 5 ? 'абонента' : 'абонентов') ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </article>
    </section>
</div>
