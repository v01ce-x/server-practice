<?php

use Model\User;

require_once __DIR__ . '/_helpers.php';

$authRole = $authRole ?? User::ROLE_SYSTEM_ADMIN;
$formData = $formData ?? ['login' => ''];
$messages = $messages ?? [];
$messageType = $messageType ?? 'error';
$switchRole = $authRole === User::ROLE_SYSTEM_ADMIN ? User::ROLE_ADMINISTRATOR : User::ROLE_SYSTEM_ADMIN;
$switchLabel = $authRole === User::ROLE_SYSTEM_ADMIN ? 'Открыть режим администратора' : 'Перейти в режим сисадмина';

if (!empty($message)) {
    $messages[] = $message;
}
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
                <form method="post" class="inline-form">
                    <?= csrf_field() ?>
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
                    <div class="inline-actions">
                        <button class="button" type="submit">Войти в рабочее пространство</button>
                        <a class="button-secondary" href="<?= e(url('/login?auth_role=' . $switchRole)) ?>"><?= e($switchLabel) ?></a>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>
