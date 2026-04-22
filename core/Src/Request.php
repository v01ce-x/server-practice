<?php

namespace Src;

use Error;

class Request
{
    protected array $body;
    public string $method;
    public array $headers;

    public function __construct()
    {
        $this->method = strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET'));
        $this->body = array_merge($_GET, $_POST);
        $this->headers = function_exists('getallheaders') ? getallheaders() : [];
    }

    public function all(): array
    {
        return $this->body + $this->files();
    }

    public function isMethod(string $method): bool
    {
        return strtoupper($this->method) === strtoupper($method);
    }

    public function isStateChanging(): bool
    {
        return in_array($this->method, ['POST', 'PUT', 'PATCH', 'DELETE'], true);
    }

    public function set($field, $value):void
    {
        $this->body[$field] = $value;
    }

    public function get($field, $default = null)
    {
        return $this->body[$field] ?? $default;
    }

    public function has(string $field): bool
    {
        return array_key_exists($field, $this->body) && $this->body[$field] !== '';
    }

    public function files(): array
    {
        return $_FILES;
    }

    public function file(string $field): ?array
    {
        $file = $this->files()[$field] ?? null;

        return is_array($file) ? $file : null;
    }

    public function __get($key)
    {
        if (array_key_exists($key, $this->body)) {
            return $this->body[$key];
        }
        throw new Error('Accessing a non-existent property');
    }
}
