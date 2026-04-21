<?php

require_once __DIR__ . '/_helpers.php';
?>
<div class="telephony-page">
    <?php telephony_page_header(
        'Привязка номера',
        'Сохранение номера непосредственно в запись выбранного абонента.'
    ); ?>

    <?php telephony_messages($assignErrors ?? []); ?>

    <div class="two-column">
        <section class="panel">
            <h2>Выбранный абонент</h2>
            <p class="section-caption">Кому будет сохранён номер.</p>
            <div class="profile-card__avatar" style="margin-top: 18px;"><?= e($subscriber->initials ?: telephony_initials($subscriber->full_name)) ?></div>
            <div class="profile-card__name"><?= e($subscriber->full_name) ?></div>
            <div class="profile-card__meta"><?= e($subscriber->department?->name ?? 'Без подразделения') ?></div>
            <div class="profile-card__details">
                <div>Текущий номер: <?= e($currentPhone?->number ?? 'не назначен') ?></div>
                <div>Помещение: <?= e($currentPhone?->room?->name ?? 'не выбрано') ?></div>
            </div>
            <div class="inline-actions" style="margin-top: 18px;">
                <a class="button-secondary" href="<?= e(url('/subscribers/' . $subscriber->id)) ?>">Карточка абонента</a>
            </div>
        </section>

        <section class="panel">
            <h2><?= $currentPhone ? 'Изменить номер' : 'Новый номер' ?></h2>
            <p class="section-caption">В вашей БД номер хранится прямо в таблице `phones` вместе с `subscriber_id`.</p>
            <form method="post" class="inline-form" style="margin-top: 18px;">
                <div class="field">
                    <label for="assign-number">Номер телефона</label>
                    <input class="field-control" id="assign-number" name="number" type="text"
                           value="<?= e($formData['number'] ?? '') ?>">
                </div>
                <div class="field">
                    <label for="assign-room">Помещение</label>
                    <select class="field-select" id="assign-room" name="room_id">
                        <option value="">Выберите помещение</option>
                        <?php foreach (($rooms ?? []) as $room): ?>
                            <option value="<?= e($room->id) ?>" <?= (string)$room->id === (string)($formData['room_id'] ?? '') ? 'selected' : '' ?>>
                                <?= e($room->name . ' • ' . ($room->type ?: 'Без типа')) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="inline-actions">
                    <button class="button" type="submit"><?= $currentPhone ? 'Обновить номер' : 'Сохранить номер' ?></button>
                </div>
            </form>
        </section>
    </div>
</div>
