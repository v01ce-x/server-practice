<?php

require_once __DIR__ . '/_helpers.php';
?>
<div class="telephony-page">
    <?php telephony_page_header('Подразделения и помещения', 'Справочники с максимально простой структурой.', $query ?? ''); ?>

    <div class="toolbar">
        <a class="button" href="<?= e(url('/directories?create=department')) ?>">Добавить подразделение</a>
        <a class="button-secondary" href="<?= e(url('/directories?create=room')) ?>">Добавить помещение</a>
    </div>

    <?php if (!empty($showDepartmentForm) || !empty($showRoomForm) || !empty($departmentErrors) || !empty($roomErrors)): ?>
        <div class="two-column">
            <section class="panel">
                <h2>Новое подразделение</h2>
                <p class="section-caption">Название и вид подразделения.</p>
                <div class="inline-form" style="margin-top: 20px;">
                    <?php telephony_messages($departmentErrors ?? []); ?>
                    <form method="post" class="inline-form">
                        <input type="hidden" name="form" value="create_department">
                        <div class="field">
                            <label for="department-name">Название подразделения</label>
                            <input class="field-control" id="department-name" name="name" type="text"
                                   value="<?= e($departmentData['name'] ?? '') ?>">
                        </div>
                        <div class="field">
                            <label for="department-type">Вид подразделения</label>
                            <input class="field-control" id="department-type" name="type" type="text"
                                   value="<?= e($departmentData['type'] ?? '') ?>" placeholder="Коммерческий">
                        </div>
                        <div class="inline-actions">
                            <button class="button" type="submit">Сохранить подразделение</button>
                        </div>
                    </form>
                </div>
            </section>

            <section class="panel">
                <h2>Новое помещение</h2>
                <p class="section-caption">Название и тип помещения.</p>
                <div class="inline-form" style="margin-top: 20px;">
                    <?php telephony_messages($roomErrors ?? []); ?>
                    <form method="post" class="inline-form">
                        <input type="hidden" name="form" value="create_room">
                        <div class="field">
                            <label for="room-name">Название или номер помещения</label>
                            <input class="field-control" id="room-name" name="room_name" type="text"
                                   value="<?= e($roomData['name'] ?? '') ?>">
                        </div>
                        <div class="field">
                            <label for="room-type">Вид помещения</label>
                            <input class="field-control" id="room-type" name="room_type" type="text"
                                   value="<?= e($roomData['type'] ?? '') ?>" placeholder="Кабинет">
                        </div>
                        <div class="inline-actions">
                            <button class="button" type="submit">Сохранить помещение</button>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    <?php endif; ?>

    <div class="two-column">
        <section class="list-card">
            <h2>Подразделения</h2>
            <p class="section-caption">Название и вид подразделения.</p>
            <div class="split-list" style="margin-top: 18px;">
                <?php foreach (($departments ?? []) as $department): ?>
                    <article class="row-card row-card--department">
                        <div class="row-card__title"><?= e($department->name) ?></div>
                        <div class="row-card__muted"><?= e($department->type) ?></div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="list-card">
            <h2>Помещения</h2>
            <p class="section-caption">Название и тип помещения.</p>
            <div class="split-list" style="margin-top: 18px;">
                <?php foreach (($rooms ?? []) as $room): ?>
                    <article class="row-card row-card--room">
                        <div class="row-card__title"><?= e($room->name) ?></div>
                        <div class="row-card__muted"><?= e($room->full_label) ?></div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    </div>
</div>
