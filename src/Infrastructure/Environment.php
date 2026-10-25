<?php

declare(strict_types=1);

namespace App\Infrastructure;

use RuntimeException;

final class Environment
{
    /** @var array<string, string> */
    private static array $values = [];

    public static function load(string $path): void
    {
        if (!is_file($path)) {
            return;
        }

        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            [$key, $value] = array_pad(explode('=', $line, 2), 2, '');
            $key = trim($key);

            if ($key !== '') {
                self::$values[$key] = trim($value);
            }
        }
    }

    public static function get(string $key): ?string
    {
        $value = getenv($key);

        if ($value !== false && $value !== '') {
            return $value;
        }

        return self::$values[$key] ?? null;
    }

    public static function required(string $key): string
    {
        $value = self::get($key);

        if ($value === null || $value === '') {
            throw new RuntimeException(sprintf('Falta la variable de entorno %s.', $key));
        }

        return $value;
    }
}
