<?php

declare(strict_types=1);

namespace App\Http;

use JsonException;

final class JsonResponse implements Response
{
    /** @param array<string, mixed> $data */
    public function __construct(
        private readonly array $data,
        private readonly int $statusCode = 200,
        private readonly array $additionalHeaders = [],
    ) {
    }

    public function statusCode(): int
    {
        return $this->statusCode;
    }

    /** @return array<string, string> */
    public function headers(): array
    {
        return [
            'Content-Type' => 'application/json; charset=utf-8',
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'DENY',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            ...$this->additionalHeaders,
        ];
    }

    public function body(): string
    {
        try {
            return json_encode(
                $this->data,
                JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
            );
        } catch (JsonException) {
            return '{"error":"No se pudo generar la respuesta."}';
        }
    }

    public function send(): void
    {
        http_response_code($this->statusCode());

        foreach ($this->headers() as $name => $value) {
            header(sprintf('%s: %s', $name, $value));
        }

        echo $this->body();
    }
}
