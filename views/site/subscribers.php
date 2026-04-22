<?php

require_once __DIR__ . '/_helpers.php';

$currentQuery = $query ?? '';
$currentDepartment = $departmentFilter ?? '';
$currentState = $stateFilter ?? 'all';
?>
<div class="telephony-page">
    <?php telephony_page_header(
        'Абоненты',
        'Реестр сотрудников с быстрым переходом в карточку и на привязку номера.',
        $currentQuery,
        ['department' => $currentDepartment, 'state' => $currentState]
    ); ?>

    <div class="toolbar">
        <a class="button" href="<?= e(url('/subscribers?create=1')) ?>">Добавить абонента</a>
        <form class="toolbar__group" method="get">
            <?php if ($currentQuery !== ''): ?>
                <input type="hidden" name="q" value="<?= e($currentQuery) ?>">
            <?php endif; ?>
            <select class="field-select" name="department" onchange="this.form.submit()">
                <option value="">Все подразделения</option>
                <?php foreach (($departments ?? []) as $department): ?>
                    <option value="<?= e($department->id) ?>" <?= (string)$department->id === (string)$currentDepartment ? 'selected' : '' ?>>
                        <?= e($department->name) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
        <div class="toolbar__group">
            <?php
            $stateLinks = [
                'all' => 'Все',
                'with_phone' => 'С номером',
                'without_phone' => 'Без номера',
            ];
            foreach ($stateLinks as $stateKey => $stateLabel):
                $params = http_build_query(array_filter([
                    'state' => $stateKey,
                    'department' => $currentDepartment,
                    'q' => $currentQuery,
                ], static fn ($value) => $value !== ''));
                ?>
                <a class="filter-chip <?= $currentState === $stateKey ? 'is-active' : '' ?>"
                   href="<?= e(url('/subscribers' . ($params ? '?' . $params : ''))) ?>"><?= e($stateLabel) ?></a>
            <?php endforeach; ?>
        </div>
    </div>

    <?php if (!empty($showCreateForm) || !empty($createErrors)): ?>
        <section class="panel">
            <h2>Новый абонент</h2>
            <p class="section-caption">Добавление сотрудника в систему внутренней телефонной связи.</p>
            <div class="inline-form" style="margin-top: 20px;">
                <?php telephony_messages($createErrors ?? []); ?>
                <form method="post" class="inline-form">
                    <input type="hidden" name="form" value="create_subscriber">
                    <div class="field-grid">
                        <div class="field">
                            <label for="subscriber-last-name">Фамилия</label>
                            <input class="field-control" id="subscriber-last-name" name="last_name" type="text"
                                   value="<?= e($createData['last_name'] ?? '') ?>" required>
                        </div>
                        <div class="field">
                            <label for="subscriber-first-name">Имя</label>
                            <input class="field-control" id="subscriber-first-name" name="first_name" type="text"
                                   value="<?= e($createData['first_name'] ?? '') ?>" required>
                        </div>
                        <div class="field">
                            <label for="subscriber-middle-name">Отчество</label>
                            <input class="field-control" id="subscriber-middle-name" name="middle_name" type="text"
                                   value="<?= e($createData['middle_name'] ?? '') ?>">
                        </div>
                        <div class="field">
                            <label for="subscriber-birth-date">Дата рождения</label>
                            <input class="field-control" id="subscriber-birth-date" name="birth_date" type="text"
                                   value="<?= e($createData['birth_date'] ?? '') ?>" placeholder="12.01.2007"
                                   inputmode="numeric" maxlength="10" pattern="\d{2}\.\d{2}\.\d{4}"
                                   title="Введите дату в формате ДД.ММ.ГГГГ" data-date-input="true" required>
                        </div>
                        <div class="field">
                            <label for="subscriber-department">Подразделение</label>
                            <select class="field-select" id="subscriber-department" name="department_id" required>
                                <option value="">Выберите подразделение</option>
                                <?php foreach (($departments ?? []) as $department): ?>
                                    <option value="<?= e($department->id) ?>" <?= (string)$department->id === (string)($createData['department_id'] ?? '') ? 'selected' : '' ?>>
                                        <?= e($department->name) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="inline-actions">
                        <button class="button" type="submit">Сохранить абонента</button>
                        <a class="button-secondary" href="<?= e(url('/subscribers')) ?>">Закрыть форму</a>
                    </div>
                </form>
            </div>
        </section>
    <?php endif; ?>

    <section class="list-card">
        <h2>Список абонентов</h2>
        <p class="section-caption">Рабочая таблица без перегрузки интерфейса.</p>
        <div class="split-list" style="margin-top: 18px;">
            <?php if (isset($subscribers) && $subscribers->isNotEmpty()): ?>
                <?php foreach ($subscribers as $subscriber): ?>
                    <?php
                    $primaryNumber = $subscriber->phone?->number ?? '—';
                    $hasPhone = $subscriber->phone !== null;
                    ?>
                    <article class="row-card row-card--subscribers">
                        <div class="row-card__title"><?= e($subscriber->full_name) ?></div>
                        <div><?= e($subscriber->department->name ?? 'Без подразделения') ?></div>
                        <div class="row-card__muted"><?= e($subscriber->birth_date_formatted) ?></div>
                        <div><?= e($primaryNumber) ?></div>
                        <a class="<?= $hasPhone ? 'button' : 'button-secondary' ?>"
                           href="<?= e($hasPhone ? url('/subscribers/' . $subscriber->id) : url('/phones/assign/' . $subscriber->id)) ?>">
                            <?= $hasPhone ? 'Открыть карточку' : 'Прикрепить номер' ?>
                        </a>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="compact-item">
                    <div class="compact-item__title">Абоненты не найдены</div>
                    <div class="compact-item__meta">Измени параметры поиска или добавь нового абонента.</div>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>
