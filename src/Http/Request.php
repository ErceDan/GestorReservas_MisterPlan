<?php

declare(strict_types=1);

namespace App\Http;

final class Request
{
    /**
     * @param array<string, mixed> $query
     * @param array<string, string> $headers
     */
    public function __construct(
        private readonly string $method,
        private readonly string $path,
        private readonly array $query = [],
        private readonly string $body = '',
        private readonly array $headers = [],
    ) {
    }

    public static function fromGlobals(): self
    {
        $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($requestUri, PHP_URL_PATH) ?: '/';
        $headers = [];

        foreach ($_SERVER as $name => $value) {
            if (str_starts_with($name, 'HTTP_') && is_string($value)) {
                $headerName = str_replace('_', '-', strtolower(substr($name, 5)));
                $headers[$headerName] = $value;
            }
        }

        return new self(
            strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET'),
            self::normalizePath($path),
            $_GET,
            file_get_contents('php://input') ?: '',
            $headers,
        );
    }

    public function method(): string
    {
        return $this->method;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function query(string $name): ?string
    {
        $value = $this->query[$name] ?? null;

        return is_scalar($value) ? (string) $value : null;
    }

    public function body(): string
    {
        return $this->body;
    }

    public function header(string $name): ?string
    {
        return $this->headers[strtolower($name)] ?? null;
    }

    private static function normalizePath(string $path): string
    {
        $normalizedPath = '/' . trim($path, '/');

        return $normalizedPath === '/' ? $normalizedPath : rtrim($normalizedPath, '/');
    }
}
