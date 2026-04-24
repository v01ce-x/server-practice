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

    public function getRoutePath(): string
    {
        $routes = trim((string)($this->path['routes'] ?? ''), '/');
        return $routes !== '' ? '/' . $routes : '';
    }

    public function getAuthClassName(): string
    {
        return $this->app['auth'] ?? '';
    }

    public function getIdentityClassName(): string
    {
        return $this->app['identity'] ?? '';
    }

    public function removeAppMiddleware(string $key): void
    {
        if (!isset($this->_settings['app']['routeAppMiddleware']) || !is_array($this->_settings['app']['routeAppMiddleware'])) {
            return;
        }

        unset($this->_settings['app']['routeAppMiddleware'][$key]);
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

    public function getDbSetting(): array
    {
        return $this->db ?? [];
    }
}
