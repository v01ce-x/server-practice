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
        $this->headers = function_exists('getallheaders') ? getallheaders() : [];
        $this->body = array_merge($_GET, $_POST, $this->jsonBody());
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

    public function path(): string
    {
        $uri = rawurldecode((string)($_SERVER['REQUEST_URI'] ?? '/'));

        if (($queryPos = strpos($uri, '?')) !== false) {
            $uri = substr($uri, 0, $queryPos);
        }

        $rootPath = '';
        if (function_exists('app')) {
            try {
                $rootPath = (string)app()->settings->getRootPath();
            } catch (\Throwable) {
                $rootPath = '';
            }
        }

        if ($rootPath !== '' && str_starts_with($uri, $rootPath)) {
            $uri = substr($uri, strlen($rootPath)) ?: '/';
        }

        return $uri === '' ? '/' : $uri;
    }

    public function isApi(): bool
    {
        $path = $this->path();

        return $path === '/api' || $path === '/api/' || str_starts_with($path, '/api/');
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

    private function jsonBody(): array
    {
        if (!str_contains(strtolower($this->header('Content-Type') ?? ''), 'application/json')) {
            return [];
        }

        $rawBody = file_get_contents('php://input');
        if (($rawBody === false || trim($rawBody) === '') && PHP_SAPI === 'cli') {
            $rawBody = file_get_contents('php://stdin');
        }

        if ($rawBody === false || trim($rawBody) === '') {
            return [];
        }

        $decoded = json_decode($rawBody, true);
        return is_array($decoded) ? $decoded : [];
    }

    public function header(string $name): ?string
    {
        foreach ($this->headers as $headerName => $value) {
            if (strcasecmp((string)$headerName, $name) === 0) {
                return is_string($value) ? $value : null;
            }
        }

        $serverKey = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        $redirectServerKey = 'REDIRECT_' . $serverKey;
        if ($name === 'Content-Type') {
            return $_SERVER['CONTENT_TYPE'] ?? $_SERVER[$serverKey] ?? $_SERVER[$redirectServerKey] ?? null;
        }

        return $_SERVER[$serverKey] ?? $_SERVER[$redirectServerKey] ?? null;
    }

    public function bearerToken(): ?string
    {
        $header = $this->header('Authorization');
        if (!is_string($header)) {
            return null;
        }

        if (preg_match('/^\s*Bearer\s+(\S+)\s*$/i', $header, $matches) !== 1) {
            return null;
        }

        return trim((string)$matches[1]);
    }

    public function __get($key)
    {
        if (array_key_exists($key, $this->body)) {
            return $this->body[$key];
        }
        throw new Error('Accessing a non-existent property');
    }
}
