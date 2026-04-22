<?php

use Model\User;
use Src\Session;

$isAuthPage = (bool)($authPage ?? false);
$flash = Session::pull('_flash');
$currentUser = app()->auth::user();
$activeMenu = $activeMenu ?? '';

$menuItems = [
    [
        'key' => 'dashboard',
        'label' => 'Обзор',
        'url' => url('/dashboard'),
        'roles' => [User::ROLE_ADMINISTRATOR, User::ROLE_SYSTEM_ADMIN],
    ],
    [
        'key' => 'subscribers',
        'label' => 'Абоненты',
        'url' => url('/subscribers'),
        'roles' => [User::ROLE_SYSTEM_ADMIN],
    ],
    [
        'key' => 'phones',
        'label' => 'Телефоны',
        'url' => url('/phones'),
        'roles' => [User::ROLE_SYSTEM_ADMIN],
    ],
    [
        'key' => 'directories',
        'label' => 'Помещения и отделы',
        'url' => url('/directories'),
        'roles' => [User::ROLE_SYSTEM_ADMIN],
    ],
    [
        'key' => 'reports',
        'label' => 'Отчёты',
        'url' => url('/reports'),
        'roles' => [User::ROLE_SYSTEM_ADMIN],
    ],
    [
        'key' => 'admins',
        'label' => 'Системные админы',
        'url' => url('/admins'),
        'roles' => [User::ROLE_ADMINISTRATOR],
    ],
];
?><!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>PBX HUB</title>
    <style>
        :root {
            --bg: #f4f7fa;
            --surface: #ffffff;
            --surface-muted: #eef3f7;
            --surface-soft: #e9f1fb;
            --border: #d9e2ec;
            --border-soft: rgba(255, 255, 255, 0.08);
            --primary: #177d74;
            --primary-soft: #e7f6f3;
            --success: #1f8f53;
            --success-soft: #e8f7ec;
            --warning: #c97705;
            --warning-soft: #fff4e5;
            --info: #2f6fe4;
            --info-soft: #e9f1fb;
            --danger: #b42318;
            --danger-soft: #fff1f1;
            --sidebar: #183346;
            --sidebar-muted: #abc0cf;
            --text: #1f2937;
            --text-muted: #6b7280;
            --radius-lg: 18px;
            --radius-md: 14px;
            --radius-sm: 12px;
            --shadow-soft: 0 20px 45px rgba(19, 43, 61, 0.05);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background: linear-gradient(180deg, #f7fafc 0%, #edf3f8 100%);
            color: var(--text);
            font: 14px/1.45 "Inter", "Segoe UI", sans-serif;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        button,
        input,
        select,
        textarea {
            font: inherit;
        }

        .app-shell {
            display: grid;
            grid-template-columns: 228px minmax(0, 1fr);
            gap: 0;
            max-width: 1440px;
            margin: 0 auto;
            min-height: 100vh;
            padding-left: 80px;
        }

        .sidebar {
            background: var(--sidebar);
            color: #fff;
            padding: 24px 18px;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .sidebar__brand {
            font-size: 28px;
            font-weight: 800;
            line-height: 1.4;
        }

        .sidebar__caption {
            margin-top: 8px;
            color: var(--sidebar-muted);
            font-size: 13px;
            line-height: 1.4;
            max-width: 190px;
        }

        .sidebar__role {
            margin-top: 20px;
            display: inline-flex;
            align-items: center;
            padding: 8px 14px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.08);
            color: #fff;
            font-size: 12px;
        }

        .sidebar__nav {
            margin-top: 34px;
            display: grid;
            gap: 10px;
        }

        .sidebar__link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 14px;
            border-radius: 12px;
            color: #fff;
            transition: background-color .15s ease, color .15s ease, opacity .15s ease;
        }

        .sidebar__link::before {
            content: "";
            width: 14px;
            height: 14px;
            border-radius: 4px;
            background: currentColor;
            opacity: .8;
            flex-shrink: 0;
        }

        .sidebar__link span {
            max-width: 140px;
        }

        .sidebar__link:hover {
            background: rgba(255, 255, 255, 0.08);
        }

        .sidebar__link.is-active {
            background: var(--primary-soft);
            color: var(--primary);
            font-weight: 600;
        }

        .sidebar__link.is-disabled {
            opacity: .45;
            cursor: not-allowed;
        }

        .sidebar__footer {
            margin-top: auto;
            padding: 14px;
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.08);
            display: grid;
            gap: 4px;
        }

        .sidebar__footer-name {
            font-weight: 700;
        }

        .sidebar__footer-status {
            color: var(--text-muted);
        }

        .sidebar__footer-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-top: 8px;
        }

        .sidebar__logout {
            color: #fff;
            font-size: 12px;
            opacity: .85;
        }

        .workspace {
            padding: 34px 36px 56px 28px;
        }

        .telephony-page {
            display: grid;
            gap: 22px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 24px;
        }

        .page-header h1 {
            margin: 0;
            font-size: 28px;
            line-height: 1.1;
        }

        .page-header p {
            margin: 10px 0 0;
            color: var(--text-muted);
            font-size: 14px;
        }

        .page-header__tools {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .search-form {
            width: min(220px, 100%);
        }

        .search-form input,
        .field-control,
        .field-select,
        .field-textarea {
            width: 100%;
            min-height: 44px;
            border: 1px solid var(--border);
            border-radius: 12px;
            background: #fff;
            color: var(--text);
            padding: 12px 14px;
            outline: none;
            transition: border-color .15s ease, box-shadow .15s ease;
        }

        .search-form input::placeholder,
        .field-control::placeholder,
        .field-textarea::placeholder {
            color: var(--text-muted);
        }

        .search-form input:focus,
        .field-control:focus,
        .field-select:focus,
        .field-textarea:focus {
            border-color: rgba(23, 125, 116, 0.45);
            box-shadow: 0 0 0 4px rgba(23, 125, 116, 0.12);
        }

        .mode-pill,
        .filter-chip,
        .status-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 32px;
            padding: 8px 14px;
            border-radius: 999px;
            font-size: 12px;
            line-height: 1;
        }

        .mode-pill {
            background: var(--success-soft);
            color: var(--success);
            font-weight: 600;
        }

        .toolbar,
        .toolbar__group {
            display: flex;
            gap: 12px;
            align-items: center;
            flex-wrap: wrap;
        }

        .toolbar--end {
            justify-content: flex-end;
        }

        .panel,
        .metric-card,
        .list-card,
        .profile-card,
        .auth-card,
        .auth-side {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-soft);
        }

        .panel,
        .list-card,
        .profile-card {
            padding: 22px 24px;
        }

        .panel h2,
        .panel h3,
        .list-card h2,
        .list-card h3,
        .profile-card h2,
        .profile-card h3 {
            margin: 0;
            font-size: 28px;
            line-height: 1.1;
        }

        .section-caption {
            margin: 8px 0 0;
            color: var(--text-muted);
        }

        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 18px;
        }

        .metrics-grid--reports {
            grid-template-columns: repeat(3, minmax(0, 220px));
        }

        .metric-card {
            padding: 20px;
        }

        .metric-card__value {
            font-size: 28px;
            font-weight: 700;
            line-height: 1.2;
        }

        .metric-card__label {
            margin-top: 8px;
        }

        .two-column {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
            gap: 18px;
        }

        .two-column--admin {
            grid-template-columns: minmax(0, 2.2fr) minmax(320px, 1fr);
        }

        .three-column {
            display: grid;
            grid-template-columns: 300px minmax(0, 1fr);
            gap: 18px;
        }

        .split-list {
            display: grid;
            gap: 12px;
        }

        .row-card {
            display: grid;
            gap: 16px;
            align-items: center;
            padding: 14px 16px;
            border: 1px solid var(--border);
            border-radius: 12px;
            background: #fff;
        }

        .row-card--subscribers,
        .row-card--phones,
        .row-card--admins {
            grid-template-columns: minmax(190px, 1.35fr) minmax(140px, 1fr) minmax(120px, .8fr) minmax(90px, .6fr) auto;
        }

        .row-card--admins {
            grid-template-columns: minmax(180px, 1.2fr) minmax(180px, 1fr) minmax(160px, .9fr) auto;
        }

        .row-card--room,
        .row-card--department,
        .row-card--assignment {
            grid-template-columns: minmax(120px, .6fr) minmax(180px, 1fr);
        }

        .row-card__title {
            font-weight: 700;
        }

        .row-card__muted {
            color: var(--text-muted);
        }

        .button,
        .button-secondary,
        .button-ghost {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 44px;
            padding: 11px 20px;
            border-radius: 12px;
            border: 1px solid transparent;
            cursor: pointer;
            transition: transform .15s ease, box-shadow .15s ease, border-color .15s ease, background-color .15s ease;
            white-space: nowrap;
        }

        .button:hover,
        .button-secondary:hover,
        .button-ghost:hover {
            transform: translateY(-1px);
        }

        .button {
            background: var(--primary);
            color: #fff;
            font-weight: 600;
        }

        .button-secondary {
            background: #fff;
            color: var(--text);
            border-color: var(--border);
            font-weight: 600;
        }

        .button-ghost {
            background: var(--surface-muted);
            color: var(--text-muted);
            border-color: var(--border);
        }

        .button--full {
            width: 100%;
        }

        .stack-list {
            display: grid;
            gap: 10px;
        }

        .notice {
            padding: 12px 14px;
            border-radius: 12px;
            border: 1px solid transparent;
        }

        .notice--error {
            background: var(--danger-soft);
            border-color: rgba(180, 35, 24, 0.15);
            color: var(--danger);
        }

        .notice--success {
            background: var(--success-soft);
            border-color: rgba(31, 143, 83, 0.15);
            color: var(--success);
        }

        .filter-chip {
            border: 1px solid var(--border);
            background: var(--surface-muted);
            color: var(--text-muted);
        }

        .filter-chip.is-active {
            background: var(--primary-soft);
            border-color: transparent;
            color: var(--primary);
            font-weight: 600;
        }

        .status-badge--neutral {
            background: var(--surface-muted);
            color: var(--text-muted);
        }

        .status-badge--success {
            background: var(--success-soft);
            color: var(--success);
        }

        .status-badge--info {
            background: var(--info-soft);
            color: var(--info);
        }

        .status-badge--warning {
            background: var(--warning-soft);
            color: var(--warning);
        }

        .status-badge--error {
            background: var(--danger-soft);
            color: var(--danger);
        }

        .field-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px 18px;
        }

        .field {
            display: grid;
            gap: 8px;
        }

        .field label {
            color: var(--text-muted);
            font-size: 12px;
            font-weight: 600;
        }

        .field-textarea {
            min-height: 110px;
            resize: vertical;
        }

        .inline-form {
            display: grid;
            gap: 18px;
        }

        .inline-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .profile-card__avatar,
        .select-card__avatar {
            width: 74px;
            height: 74px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: var(--info-soft);
            color: var(--info);
            font-size: 24px;
            font-weight: 700;
        }

        .profile-card__name {
            margin-top: 14px;
            font-size: 20px;
            font-weight: 700;
        }

        .profile-card__meta {
            margin-top: 6px;
            color: var(--text-muted);
        }

        .profile-card__details {
            margin-top: 18px;
            display: grid;
            gap: 6px;
        }

        .progress-list {
            display: grid;
            gap: 18px;
            margin-top: 20px;
        }

        .progress-list__row {
            display: grid;
            gap: 8px;
        }

        .progress-list__top {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            font-style: italic;
            font-weight: 600;
        }

        .progress-list__track {
            height: 10px;
            border-radius: 999px;
            background: var(--info-soft);
            overflow: hidden;
        }

        .progress-list__fill {
            height: 100%;
            border-radius: inherit;
            background: var(--primary);
        }

        .compact-list {
            display: grid;
            gap: 12px;
            margin-top: 18px;
        }

        .compact-item {
            padding: 14px 16px;
            border-radius: 14px;
            border: 1px solid var(--border);
            background: var(--surface-muted);
        }

        .compact-item__title {
            font-weight: 700;
        }

        .compact-item__meta {
            margin-top: 4px;
            color: var(--text-muted);
        }

        .selector-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(118px, 1fr));
            gap: 14px;
        }

        .selector-card {
            position: relative;
        }

        .selector-card input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .selector-card__body {
            display: grid;
            gap: 6px;
            min-height: 110px;
            padding: 17px;
            border: 1px solid var(--border);
            border-radius: 14px;
            background: var(--surface-muted);
            transition: border-color .15s ease, background-color .15s ease, box-shadow .15s ease;
            cursor: pointer;
        }

        .selector-card input:checked + .selector-card__body {
            border-color: rgba(23, 125, 116, 0.4);
            background: var(--primary-soft);
            box-shadow: 0 0 0 4px rgba(23, 125, 116, 0.1);
        }

        .selector-card__number {
            font-size: 30px;
            font-weight: 700;
            line-height: 1;
        }

        .selector-card__meta {
            color: var(--text-muted);
        }

        .auth-layout {
            max-width: 1440px;
            min-height: 100vh;
            margin: 0 auto;
            padding-left: 80px;
        }

        .auth-shell {
            display: grid;
            grid-template-columns: 520px minmax(0, 1fr);
            min-height: 100vh;
        }

        .auth-side {
            background: var(--sidebar);
            border-radius: 0;
            border: 0;
            color: #fff;
            padding: 52px 48px;
        }

        .auth-side h1 {
            margin: 0;
            font-size: 34px;
            line-height: 1.1;
        }

        .auth-side p {
            margin: 18px 0 0;
            max-width: 360px;
            color: var(--sidebar-muted);
        }

        .auth-side__stats {
            margin-top: 42px;
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
        }

        .auth-side__stat {
            padding: 18px;
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid var(--border-soft);
        }

        .auth-side__stat strong {
            display: block;
            font-size: 28px;
            line-height: 1.2;
        }

        .auth-main {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .auth-card {
            width: 100%;
            max-width: 650px;
            padding: 32px;
        }

        .auth-card h2 {
            margin: 0;
            font-size: 28px;
            line-height: 1.1;
        }

        .auth-card p {
            margin: 10px 0 0;
            color: var(--text-muted);
        }

        .auth-card__body {
            display: grid;
            gap: 20px;
            margin-top: 28px;
        }

        .role-toggle {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .role-toggle label {
            position: relative;
            cursor: pointer;
        }

        .role-toggle input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .role-toggle span {
            display: inline-flex;
            align-items: center;
            min-height: 38px;
            padding: 8px 14px;
            border-radius: 12px;
            border: 1px solid var(--border);
            background: var(--surface-muted);
            color: var(--text-muted);
            transition: border-color .15s ease, background-color .15s ease, color .15s ease;
        }

        .role-toggle input:checked + span {
            background: var(--primary-soft);
            border-color: transparent;
            color: var(--primary);
            font-weight: 600;
        }

        .demo-list {
            display: grid;
            gap: 10px;
            padding: 0;
            margin: 0;
            list-style: none;
        }

        .demo-list li {
            padding: 12px 14px;
            border-radius: 12px;
            background: var(--surface-muted);
            border: 1px solid var(--border);
        }

        .demo-list strong {
            display: block;
            margin-bottom: 4px;
        }

        .flash-stack {
            margin-bottom: 18px;
        }

        .policies {
            display: grid;
            gap: 14px;
            margin-top: 18px;
        }

        .policy-item {
            color: var(--text);
            line-height: 1.45;
        }

        .muted {
            color: var(--text-muted);
        }

        @media (max-width: 1180px) {
            .app-shell {
                grid-template-columns: 1fr;
                padding-left: 0;
            }

            .sidebar {
                min-height: auto;
            }

            .workspace {
                padding: 24px;
            }

            .metrics-grid,
            .metrics-grid--reports,
            .two-column,
            .two-column--admin,
            .three-column {
                grid-template-columns: 1fr;
            }

            .row-card--subscribers,
            .row-card--phones,
            .row-card--admins,
            .row-card--room,
            .row-card--department,
            .row-card--assignment,
            .field-grid {
                grid-template-columns: 1fr;
            }

            .auth-layout {
                padding-left: 0;
            }

            .auth-shell {
                grid-template-columns: 1fr;
            }

            .auth-main {
                padding: 24px;
            }

            .auth-side {
                padding: 32px 24px;
            }
        }

        @media (max-width: 720px) {
            .page-header,
            .page-header__tools {
                flex-direction: column;
                align-items: stretch;
            }

            .toolbar,
            .toolbar__group,
            .inline-actions {
                flex-direction: column;
                align-items: stretch;
            }

            .button,
            .button-secondary,
            .button-ghost {
                width: 100%;
            }

            .auth-side__stats {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
<?php if ($isAuthPage): ?>
    <div class="auth-layout">
        <?php if ($flash): ?>
            <div class="workspace flash-stack">
                <div class="notice notice--<?= e($flash['type'] ?? 'success') ?>"><?= e($flash['message'] ?? '') ?></div>
            </div>
        <?php endif; ?>
        <?= $content ?? '' ?>
    </div>
<?php else: ?>
    <div class="app-shell">
        <aside class="sidebar">
            <div class="sidebar__brand">PBX HUB</div>
            <div class="sidebar__caption">Внутренняя телефонная связь</div>
            <?php if ($currentUser): ?>
                <div class="sidebar__role"><?= e($currentUser->getRoleLabel()) ?></div>
            <?php endif; ?>
            <nav class="sidebar__nav">
                <?php foreach ($menuItems as $item): ?>
                    <?php
                    $isAllowed = $currentUser && in_array($currentUser->role, $item['roles'], true);
                    $classes = ['sidebar__link'];
                    if ($item['key'] === $activeMenu) {
                        $classes[] = 'is-active';
                    }
                    if (!$isAllowed) {
                        $classes[] = 'is-disabled';
                    }
                    ?>
                    <?php if ($isAllowed): ?>
                        <a class="<?= e(implode(' ', $classes)) ?>" href="<?= e($item['url']) ?>">
                            <span><?= e($item['label']) ?></span>
                        </a>
                    <?php else: ?>
                        <span class="<?= e(implode(' ', $classes)) ?>">
                            <span><?= e($item['label']) ?></span>
                        </span>
                    <?php endif; ?>
                <?php endforeach; ?>
            </nav>
            <?php if ($currentUser): ?>
                <div class="sidebar__footer">
                    <div class="sidebar__footer-name"><?= e($currentUser->getShortName()) ?></div>
                    <div class="sidebar__footer-status"><?= e(mb_strtolower($currentUser->getStatusLabel())) ?></div>
                    <div class="sidebar__footer-actions">
                        <?= telephony_status_badge($currentUser->getStatusLabel(), $currentUser->status === User::STATUS_ACTIVE ? 'success' : 'warning') ?>
                        <a class="sidebar__logout" href="<?= e(url('/logout')) ?>">Выйти</a>
                    </div>
                </div>
            <?php endif; ?>
        </aside>
        <main class="workspace">
            <?php if ($flash): ?>
                <div class="flash-stack">
                    <div class="notice notice--<?= e($flash['type'] ?? 'success') ?>"><?= e($flash['message'] ?? '') ?></div>
                </div>
            <?php endif; ?>
            <?= $content ?? '' ?>
        </main>
    </div>
<?php endif; ?>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const dateInputs = document.querySelectorAll('[data-date-input]');

        if (dateInputs.length === 0) {
            return;
        }

        const formatDateValue = (value) => {
            const digits = value.replace(/\D/g, '').slice(0, 8);
            const parts = [];

            if (digits.length > 0) {
                parts.push(digits.slice(0, 2));
            }

            if (digits.length > 2) {
                parts.push(digits.slice(2, 4));
            }

            if (digits.length > 4) {
                parts.push(digits.slice(4, 8));
            }

            return parts.join('.');
        };

        const isValidDateValue = (value) => {
            if (!/^\d{2}\.\d{2}\.\d{4}$/.test(value)) {
                return false;
            }

            const [day, month, year] = value.split('.').map(Number);
            const date = new Date(year, month - 1, day);

            return date.getFullYear() === year
                && date.getMonth() === month - 1
                && date.getDate() === day;
        };

        const syncDateValidity = (input) => {
            if (input.value === '') {
                input.setCustomValidity('');
                return;
            }

            input.setCustomValidity(
                isValidDateValue(input.value)
                    ? ''
                    : 'Введите дату в формате ДД.ММ.ГГГГ, например 12.01.2007.'
            );
        };

        dateInputs.forEach((input) => {
            input.addEventListener('input', () => {
                input.value = formatDateValue(input.value);
                syncDateValidity(input);
            });

            input.addEventListener('blur', () => {
                syncDateValidity(input);
            });
        });

        document.querySelectorAll('form[method="post"]').forEach((form) => {
            form.addEventListener('submit', (event) => {
                form.querySelectorAll('[data-date-input]').forEach((input) => {
                    input.value = formatDateValue(input.value);
                    syncDateValidity(input);
                });

                if (!form.checkValidity()) {
                    event.preventDefault();
                    form.reportValidity();
                }
            });
        });
    });
</script>
</body>
</html>
