<?php

declare(strict_types=1);

namespace App\Infrastructure;

use App\Http\JsonResponse;
use App\Exception\NotFoundException;
use App\Exception\TransicionInvalidaException;
use App\Exception\ValidationException;
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

                if ($exception instanceof ValidationException) {
                    (new JsonResponse(['error' => $exception->getMessage(), 'errors' => $exception->errors()], 400))->send();

                    return;
                }

                if ($exception instanceof NotFoundException) {
                    (new JsonResponse(['error' => $exception->getMessage()], 404))->send();

                    return;
                }

                if ($exception instanceof TransicionInvalidaException) {
                    (new JsonResponse(['error' => $exception->getMessage()], 400))->send();

                    return;
                }

                (new JsonResponse(['error' => 'Error interno del servidor.'], 500))->send();
            },
        );
    }
}
