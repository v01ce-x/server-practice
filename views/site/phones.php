<?php

require_once __DIR__ . '/_helpers.php';

$currentQuery = $query ?? '';
?>
<div class="telephony-page">
    <?php telephony_page_header('Телефоны', 'Реестр номеров, закреплённых за абонентами и помещениями.', $currentQuery); ?>

    <div class="toolbar">
        <a class="button" href="<?= e(url('/phones?create=1')) ?>">Добавить номер</a>
    </div>

    <?php if (!empty($showCreateForm) || !empty($createErrors)): ?>
        <section class="panel">
            <h2>Новый телефон</h2>
            <p class="section-caption">Добавление номера сразу с привязкой к абоненту.</p>
            <div class="inline-form" style="margin-top: 20px;">
                <?php telephony_messages($createErrors ?? []); ?>
                <form method="post" class="inline-form">
                    <input type="hidden" name="form" value="create_phone">
                    <div class="field-grid">
                        <div class="field">
                            <label for="phone-number">Номер телефона</label>
                            <input class="field-control" id="phone-number" name="number" type="text"
                                   value="<?= e($createData['number'] ?? '') ?>" required>
                        </div>
                        <div class="field">
                            <label for="phone-room">Помещение</label>
                            <select class="field-select" id="phone-room" name="room_id" required>
                                <option value="">Выберите помещение</option>
                                <?php foreach (($rooms ?? []) as $room): ?>
                                    <option value="<?= e($room->id) ?>" <?= (string)$room->id === (string)($createData['room_id'] ?? '') ? 'selected' : '' ?>>
                                        <?= e($room->name . ' • ' . ($room->type ?: 'Без типа')) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="field">
                            <label for="phone-subscriber">Абонент</label>
                            <select class="field-select" id="phone-subscriber" name="subscriber_id" required>
                                <option value="">Выберите абонента</option>
                                <?php foreach (($subscribers ?? []) as $subscriber): ?>
                                    <option value="<?= e($subscriber->id) ?>" <?= (string)$subscriber->id === (string)($createData['subscriber_id'] ?? '') ? 'selected' : '' ?>>
                                        <?= e($subscriber->full_name . ' • ' . ($subscriber->department?->name ?? 'Без подразделения')) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="inline-actions">
                        <button class="button" type="submit">Сохранить номер</button>
                        <a class="button-secondary" href="<?= e(url('/phones')) ?>">Закрыть форму</a>
                    </div>
                </form>
            </div>
        </section>
    <?php endif; ?>

    <section class="list-card">
        <h2>Номерной фонд</h2>
        <p class="section-caption">В текущей схеме у каждой записи сразу есть помещение и абонент.</p>
        <div class="split-list" style="margin-top: 18px;">
            <?php if (isset($phones) && $phones->isNotEmpty()): ?>
                <?php foreach ($phones as $phone): ?>
                    <?php $assignedSubscriber = $phone->subscriber; ?>
                    <article class="row-card row-card--phones">
                        <div class="row-card__title"><?= e($phone->number) ?></div>
                        <div><?= e($phone->room?->name ?? 'Без помещения') ?></div>
                        <div><?= e($assignedSubscriber?->full_name ?? '—') ?></div>
                        <div><?= telephony_status_badge('закреплён', 'info') ?></div>
                        <?php if ($assignedSubscriber): ?>
                            <a class="button-secondary" href="<?= e(url('/subscribers/' . $assignedSubscriber->id)) ?>">Открыть</a>
                        <?php else: ?>
                            <span class="button-ghost">Без абонента</span>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="compact-item">
                    <div class="compact-item__title">Номера не найдены</div>
                    <div class="compact-item__meta">Измени параметры поиска или добавь новую запись.</div>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>
