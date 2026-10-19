<?php

declare(strict_types=1);

namespace App\Infrastructure;

use App\Http\JsonResponse;
use ErrorException;
use Throwable;

final class ErrorHandler
{
    public static function register(): void
    {
        set_error_handler(
            static function (int $severity, string $message, string $file, int $line): bool {
                if ((error_reporting() & $severity) === 0) {
                    return false;
                }

                throw new ErrorException($message, 0, $severity, $file, $line);
            },
        );

        set_exception_handler(
            static function (Throwable $exception): void {
                error_log((string) $exception);

                (new JsonResponse(['error' => 'Error interno del servidor.'], 500))->send();
            },
        );
    }
}
