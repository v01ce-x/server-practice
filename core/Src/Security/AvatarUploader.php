<?php

namespace Src\Security;

use RuntimeException;

class AvatarUploader
{
    private const MAX_FILE_SIZE = 2097152;
    private const ALLOWED_MIME_TYPES = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
    ];

    public static function store(?array $file, ?string $currentAvatarPath = null): string
    {
        if (!is_array($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            throw new RuntimeException('Выберите файл аватара.');
        }

        self::assertUploadSucceeded((int)($file['error'] ?? UPLOAD_ERR_NO_FILE));

        $tmpName = (string)($file['tmp_name'] ?? '');
        if ($tmpName === '' || !is_uploaded_file($tmpName)) {
            throw new RuntimeException('Файл аватара загружен некорректно.');
        }

        $fileSize = (int)($file['size'] ?? 0);
        if ($fileSize <= 0 || $fileSize > self::MAX_FILE_SIZE) {
            throw new RuntimeException('Аватар должен быть не больше 2 МБ.');
        }

        $mimeType = self::detectMimeType($tmpName);
        $extension = self::ALLOWED_MIME_TYPES[$mimeType] ?? null;
        if ($extension === null) {
            throw new RuntimeException('Допустимы только изображения JPG, PNG, GIF или WebP.');
        }

        if (@getimagesize($tmpName) === false) {
            throw new RuntimeException('Загруженный файл не является корректным изображением.');
        }

        $directory = self::avatarDirectory();
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new RuntimeException('Не удалось подготовить каталог для аватаров.');
        }

        $fileName = bin2hex(random_bytes(24)) . '.' . $extension;
        $targetPath = $directory . DIRECTORY_SEPARATOR . $fileName;
        if (!move_uploaded_file($tmpName, $targetPath)) {
            throw new RuntimeException('Не удалось сохранить загруженный аватар.');
        }

        @chmod($targetPath, 0644);
        self::deletePreviousAvatar($currentAvatarPath);

        return 'uploads/avatars/' . $fileName;
    }

    private static function assertUploadSucceeded(int $errorCode): void
    {
        if ($errorCode === UPLOAD_ERR_OK) {
            return;
        }

        $message = match ($errorCode) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Аватар должен быть не больше 2 МБ.',
            UPLOAD_ERR_PARTIAL => 'Файл аватара загрузился не полностью.',
            UPLOAD_ERR_NO_TMP_DIR => 'На сервере не настроен временный каталог для загрузок.',
            UPLOAD_ERR_CANT_WRITE => 'Сервер не смог записать загруженный аватар на диск.',
            UPLOAD_ERR_EXTENSION => 'Загрузка аватара остановлена расширением PHP.',
            default => 'Не удалось обработать загрузку аватара.',
        };

        throw new RuntimeException($message);
    }

    private static function detectMimeType(string $path): string
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo === false) {
            throw new RuntimeException('Не удалось определить тип файла аватара.');
        }

        $mimeType = finfo_file($finfo, $path) ?: '';
        finfo_close($finfo);

        return (string)$mimeType;
    }

    private static function deletePreviousAvatar(?string $currentAvatarPath): void
    {
        $currentAvatarPath = trim((string)$currentAvatarPath);
        if ($currentAvatarPath === '') {
            return;
        }

        $absolutePath = realpath(self::publicRoot() . DIRECTORY_SEPARATOR . ltrim($currentAvatarPath, '/'));
        $avatarRoot = realpath(self::avatarDirectory());

        if ($absolutePath === false || $avatarRoot === false) {
            return;
        }

        $normalizedRoot = rtrim($avatarRoot, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        if (!str_starts_with($absolutePath, $normalizedRoot) || !is_file($absolutePath)) {
            return;
        }

        @unlink($absolutePath);
    }

    private static function avatarDirectory(): string
    {
        return self::publicRoot() . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'avatars';
    }

    private static function publicRoot(): string
    {
        return dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'public';
    }
}
