<?php

if (!function_exists('telephony_hidden_inputs')) {
    function telephony_hidden_inputs(array $inputs): void
    {
        foreach ($inputs as $name => $value) {
            if ($value === null || $value === '') {
                continue;
            }
            echo '<input type="hidden" name="' . e($name) . '" value="' . e($value) . '">';
        }
    }
}

if (!function_exists('telephony_page_header')) {
    function telephony_page_header(string $title, string $subtitle, string $query = '', array $hidden = []): void
    {
        ?>
        <div class="page-header">
            <div>
                <h1><?= e($title) ?></h1>
                <p><?= e($subtitle) ?></p>
            </div>
            <div class="page-header__tools">
                <form class="search-form" method="get">
                    <?php telephony_hidden_inputs($hidden); ?>
                    <input type="search" name="q" value="<?= e($query) ?>" placeholder="Поиск">
                </form>
                <span class="mode-pill">Рабочий режим</span>
            </div>
        </div>
        <?php
    }
}

if (!function_exists('telephony_messages')) {
    function telephony_messages(array $messages, string $tone = 'error'): void
    {
        if ($messages === []) {
            return;
        }
        ?>
        <div class="stack-list">
            <?php foreach ($messages as $message): ?>
                <div class="notice notice--<?= e($tone) ?>"><?= e($message) ?></div>
            <?php endforeach; ?>
        </div>
        <?php
    }
}

if (!function_exists('telephony_status_badge')) {
    function telephony_status_badge(string $label, string $tone = 'neutral'): string
    {
        return '<span class="status-badge status-badge--' . e($tone) . '">' . e($label) . '</span>';
    }
}

if (!function_exists('telephony_initials')) {
    function telephony_initials(string $value): string
    {
        $parts = preg_split('/\s+/u', trim($value)) ?: [];
        $letters = [];
        foreach ($parts as $part) {
            if ($part === '') {
                continue;
            }
            $letters[] = mb_substr($part, 0, 1);
        }

        return mb_strtoupper(implode('', array_slice($letters, 0, 2)));
    }
}

if (!function_exists('telephony_is_active')) {
    function telephony_is_active(string $needle, string $active): bool
    {
        return $needle === $active;
    }
}
