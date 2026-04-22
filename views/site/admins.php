<?php

require_once __DIR__ . '/_helpers.php';
?>
<div class="telephony-page">
    <?php telephony_page_header('Системные администраторы', 'Экран для роли администратора системы.', $query ?? ''); ?>

    <div class="toolbar toolbar--end">
        <a class="button" href="<?= e(url('/admins?create=1')) ?>">Добавить администратора</a>
    </div>

    <?php if (!empty($showCreateForm) || !empty($createErrors)): ?>
        <section class="panel">
            <h2>Новый системный администратор</h2>
            <p class="section-caption">Администратор системы добавляет сотрудников с правами сопровождения внутренней телефонной связи.</p>
            <div class="inline-form" style="margin-top: 20px;">
                <?php telephony_messages($createErrors ?? []); ?>
                <form method="post" class="inline-form">
                    <?= csrf_field() ?>
                    <div class="field-grid">
                        <div class="field">
                            <label for="admin-login">Логин</label>
                            <input class="field-control" id="admin-login" name="login" type="text"
                                   value="<?= e($createData['login'] ?? '') ?>" placeholder="sysadmin@company.local">
                        </div>
                        <div class="field">
                            <label for="admin-password">Пароль</label>
                            <input class="field-control" id="admin-password" name="password" type="password">
                        </div>
                    </div>
                    <div class="inline-actions">
                        <button class="button" type="submit">Сохранить администратора</button>
                        <a class="button-secondary" href="<?= e(url('/admins')) ?>">Закрыть форму</a>
                    </div>
                </form>
            </div>
        </section>
    <?php endif; ?>

    <div class="two-column two-column--admin">
        <section class="list-card">
            <h2>Текущие учётные записи</h2>
            <p class="section-caption">Пользователи с административным доступом.</p>
            <div class="split-list" style="margin-top: 18px;">
                <?php if (isset($admins) && $admins->isNotEmpty()): ?>
                    <?php foreach ($admins as $admin): ?>
                        <article class="row-card row-card--admins">
                            <div class="row-card__title"><?= e($admin->login) ?></div>
                            <div class="row-card__muted">ID <?= e((string)$admin->id) ?></div>
                            <div><?= e($admin->getRoleLabel()) ?></div>
                            <div>
                                <?= telephony_status_badge($admin->getStatusLabel(), 'success') ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="compact-item">
                        <div class="compact-item__title">Системных администраторов пока нет</div>
                        <div class="compact-item__meta">Добавь первого пользователя с ролью сопровождения через форму выше.</div>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <section class="panel">
            <h2>Политика доступа</h2>
            <p class="section-caption">Ключевые ограничения из ТЗ.</p>
            <div class="policies">
                <?php foreach (($policies ?? []) as $policy): ?>
                    <div class="policy-item"><?= e($policy) ?></div>
                <?php endforeach; ?>
            </div>
        </section>
    </div>
</div>
