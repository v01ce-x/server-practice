<?php

namespace Src;

use Error;

class Settings
{
    private array $_settings;

    public function __construct(array $settings = [])
    {
        $this->_settings = $settings;
    }

    public function __get($key)
    {
        if (array_key_exists($key, $this->_settings)) {
            return $this->_settings[$key];
        }
        throw new Error('Accessing a non-existent property');
    }

    public function getRootPath(): string
    {
        $root = trim($this->path['root'] ?? '', '/');
        return $root !== '' ? '/' . $root : '';
    }

    public function getViewsPath(): string
    {
        $views = trim($this->path['views'] ?? '', '/');
        return $views !== '' ? '/' . $views : '';
    }

    public function getDbSetting(): array {
        return $this->db ?? [];
    }
}
