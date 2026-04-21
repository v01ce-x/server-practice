<?php

use Model\User;

require_once __DIR__ . '/_helpers.php';

$authRole = $authRole ?? User::ROLE_SYSTEM_ADMIN;
$formData = $formData ?? ['login' => ''];
$messageType = $messageType ?? 'error';
$switchRole = $authRole === User::ROLE_SYSTEM_ADMIN ? User::ROLE_ADMINISTRATOR : User::ROLE_SYSTEM_ADMIN;
$switchLabel = $authRole === User::ROLE_SYSTEM_ADMIN ? 'Открыть режим администратора' : 'Перейти в режим сисадмина';
?>
<div class="auth-shell">
    <section class="auth-side">
        <h1>PBX HUB</h1>
        <p>Простой внутренний интерфейс для абонентов, номеров, помещений и подразделений.</p>
        <div class="auth-side__stats">
            <?php foreach (($loginStats ?? []) as $item): ?>
                <div class="auth-side__stat">
                    <strong><?= e($item['value']) ?></strong>
                    <span><?= e($item['label']) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    <section class="auth-main">
        <div class="auth-card">
            <h2>Войти в систему</h2>
            <p>Выбери сценарий входа и продолжай работу.</p>
            <div class="auth-card__body">
                <?php if (!empty($message)): ?>
                    <?php telephony_messages([$message], $messageType); ?>
                <?php endif; ?>
                <?php if (!empty($setupHint)): ?>
                    <?php telephony_messages([$setupHint], 'success'); ?>
                <?php endif; ?>
                <form method="post" class="inline-form">
                    <div class="field">
                        <label for="login">Логин</label>
                        <input class="field-control" id="login" name="login" type="text"
                               value="<?= e($formData['login'] ?? '') ?>" placeholder="sysadmin@company.local">
                    </div>
                    <div class="field">
                        <label for="password">Пароль</label>
                        <input class="field-control" id="password" name="password" type="password"
                               placeholder="••••••••••••">
                    </div>
                    <div class="role-toggle">
                        <label>
                            <input type="radio" name="auth_role" value="<?= e(User::ROLE_SYSTEM_ADMIN) ?>"
                                   <?= $authRole === User::ROLE_SYSTEM_ADMIN ? 'checked' : '' ?>>
                            <span>Системный администратор</span>
                        </label>
                        <label>
                            <input type="radio" name="auth_role" value="<?= e(User::ROLE_ADMINISTRATOR) ?>"
                                   <?= $authRole === User::ROLE_ADMINISTRATOR ? 'checked' : '' ?>>
                            <span>Администратор системы</span>
                        </label>
                    </div>
                    <div class="inline-actions">
                        <button class="button" type="submit">Войти в рабочее пространство</button>
                        <a class="button-secondary" href="<?= e(url('/login?auth_role=' . $switchRole)) ?>"><?= e($switchLabel) ?></a>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>
