<?php

declare(strict_types=1);

namespace App\Http;

final class HtmlResponse implements Response
{
    public function __construct(private readonly string $content, private readonly int $statusCode = 200)
    {
    }

    public function statusCode(): int { return $this->statusCode; }
    public function headers(): array { return ['Content-Type' => 'text/html; charset=utf-8', 'X-Content-Type-Options' => 'nosniff']; }
    public function body(): string { return $this->content; }
    public function send(): void { http_response_code($this->statusCode); foreach ($this->headers() as $name => $value) { header("$name: $value"); } echo $this->content; }
}
