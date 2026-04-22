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
                    <?php if (($action['method'] ?? 'get') === 'post'): ?>
                        <form method="post" action="<?= e($action['url']) ?>">
                            <?= csrf_field() ?>
                            <button class="<?= !empty($action['primary']) ? 'button' : 'button-secondary' ?>" type="submit">
                                <?= e($action['label']) ?>
                            </button>
                        </form>
                    <?php else: ?>
                        <a class="<?= !empty($action['primary']) ? 'button' : 'button-secondary' ?>"
                           href="<?= e($action['url']) ?>"><?= e($action['label']) ?></a>
                    <?php endif; ?>
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

    <?php if (!empty($currentUser)): ?>
        <section class="two-column">
            <article class="panel">
                <h2>Профиль пользователя</h2>
                <p class="section-caption">Текущая учётная запись и отображаемый аватар.</p>
                <div class="profile-card" style="margin-top: 20px;">
                    <?= telephony_avatar($currentUser->getAvatarFallback(), $currentUser->avatar_url, 'profile-card__avatar', 'Аватар пользователя') ?>
                    <div class="profile-card__name"><?= e($currentUser->getShortName()) ?></div>
                    <div class="profile-card__meta"><?= e($currentUser->getRoleLabel()) ?></div>
                    <div style="margin-top: 14px;">
                        <?= telephony_status_badge($currentUser->getStatusLabel(), 'success') ?>
                    </div>
                </div>
            </article>

            <article class="panel">
                <h2>Загрузка аватара</h2>
                <p class="section-caption">Допустимы JPG, PNG, GIF и WebP. Максимальный размер файла 2 МБ.</p>
                <form method="post" action="<?= e(url('/profile/avatar')) ?>" enctype="multipart/form-data" class="inline-form" style="margin-top: 20px;">
                    <?= csrf_field() ?>
                    <div class="field">
                        <label for="avatar">Файл аватара</label>
                        <input class="field-control" id="avatar" name="avatar" type="file"
                               accept=".jpg,.jpeg,.png,.gif,.webp,image/jpeg,image/png,image/gif,image/webp">
                    </div>
                    <div class="inline-actions">
                        <button class="button" type="submit">Загрузить аватар</button>
                    </div>
                </form>
            </article>
        </section>
    <?php endif; ?>
</div>
