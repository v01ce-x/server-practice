<?php

require_once __DIR__ . '/_helpers.php';

$primaryNumber = $subscriber->phone?->number ?? '—';
?>
<div class="telephony-page">
    <?php telephony_page_header(
        'Карточка абонента',
        'Редактирование данных и просмотр назначенных телефонов.',
        $query ?? ''
    ); ?>

    <div class="three-column">
        <aside class="profile-card">
            <h2>Профиль</h2>
            <p class="section-caption">Основная информация об абоненте.</p>
            <?= telephony_avatar($subscriber->initials ?: telephony_initials($subscriber->full_name), null, 'profile-card__avatar', 'Аватар абонента') ?>
            <div class="profile-card__name"><?= e($subscriber->full_name) ?></div>
            <div class="profile-card__meta"><?= e($subscriber->department?->name ?? 'Без подразделения') ?></div>
            <div style="margin-top: 14px;"><?= telephony_status_badge('Активен', 'success') ?></div>
            <div class="profile-card__details">
                <div>Дата рождения: <?= e($subscriber->birth_date_formatted) ?></div>
                <div>Основной номер: <?= e($primaryNumber) ?></div>
            </div>
        </aside>

        <section class="panel">
            <h2>Редактирование</h2>
            <p class="section-caption">Минимальная форма для обновления записи.</p>
            <div class="inline-form" style="margin-top: 20px;">
                <?php telephony_messages($formErrors ?? []); ?>
                <form method="post" class="inline-form">
                    <?= csrf_field() ?>
                    <div class="field-grid">
                        <div class="field">
                            <label for="card-last-name">Фамилия</label>
                            <input class="field-control" id="card-last-name" name="last_name" type="text"
                                   value="<?= e($formData['last_name'] ?? '') ?>">
                        </div>
                        <div class="field">
                            <label for="card-first-name">Имя</label>
                            <input class="field-control" id="card-first-name" name="first_name" type="text"
                                   value="<?= e($formData['first_name'] ?? '') ?>">
                        </div>
                        <div class="field">
                            <label for="card-middle-name">Отчество</label>
                            <input class="field-control" id="card-middle-name" name="middle_name" type="text"
                                   value="<?= e($formData['middle_name'] ?? '') ?>">
                        </div>
                        <div class="field">
                            <label for="card-birth-date">Дата рождения</label>
                            <input class="field-control" id="card-birth-date" name="birth_date" type="text"
                                   value="<?= e($formData['birth_date'] ?? '') ?>" placeholder="12.01.2007"
                                   inputmode="numeric" maxlength="10">
                        </div>
                        <div class="field">
                            <label for="card-department">Подразделение</label>
                            <select class="field-select" id="card-department" name="department_id">
                                <?php foreach (($departments ?? []) as $department): ?>
                                    <option value="<?= e($department->id) ?>" <?= (string)$department->id === (string)($formData['department_id'] ?? '') ? 'selected' : '' ?>>
                                        <?= e($department->name) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="inline-actions">
                        <button class="button" type="submit">Сохранить изменения</button>
                        <a class="button-secondary" href="<?= e(url('/phones/assign/' . $subscriber->id)) ?>">
                            <?= $subscriber->phone ? 'Изменить номер' : 'Назначить номер' ?>
                        </a>
                    </div>
                </form>
            </div>
        </section>
    </div>

    <section class="list-card">
        <h2>Назначенный номер</h2>
        <p class="section-caption">Номер, закреплённый за абонентом в текущей базе.</p>
        <div class="split-list" style="margin-top: 18px;">
            <?php if ($subscriber->phone): ?>
                <article class="row-card row-card--assignment">
                    <div class="row-card__title"><?= e($subscriber->phone->number) ?></div>
                    <div class="row-card__muted">
                        <?= e(($subscriber->phone->room?->name ?? 'Без помещения') . ' • внутренний') ?>
                    </div>
                </article>
            <?php else: ?>
                <div class="compact-item">
                    <div class="compact-item__title">Номер пока не назначен</div>
                    <div class="compact-item__meta">Открой сценарий привязки и сохрани номер прямо в запись абонента.</div>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>
