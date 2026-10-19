<?php

declare(strict_types=1);

namespace App\Http;

use Closure;
use LogicException;

final class Router
{
    /** @var list<array{method: string, pattern: string, handler: Closure}> */
    private array $routes = [];

    /** @param callable(Request, array<string, string>): Response $handler */
    public function get(string $pattern, callable $handler): void
    {
        $this->add('GET', $pattern, $handler);
    }

    /** @param callable(Request, array<string, string>): Response $handler */
    public function post(string $pattern, callable $handler): void
    {
        $this->add('POST', $pattern, $handler);
    }

    /** @param callable(Request, array<string, string>): Response $handler */
    public function patch(string $pattern, callable $handler): void
    {
        $this->add('PATCH', $pattern, $handler);
    }

    /** @param callable(Request, array<string, string>): Response $handler */
    public function add(string $method, string $pattern, callable $handler): void
    {
        $this->routes[] = [
            'method' => strtoupper($method),
            'pattern' => $this->normalizePattern($pattern),
            'handler' => Closure::fromCallable($handler),
        ];
    }

    public function dispatch(Request $request): Response
    {
        $allowedMethods = [];

        foreach ($this->routes as $route) {
            $parameters = $this->match($route['pattern'], $request->path());

            if ($parameters === null) {
                continue;
            }

            $allowedMethods[] = $route['method'];

            if ($route['method'] !== $request->method()) {
                continue;
            }

            $response = ($route['handler'])($request, $parameters);

            if (!$response instanceof Response) {
                throw new LogicException('Un controlador debe devolver una respuesta HTTP.');
            }

            return $response;
        }

        if ($allowedMethods !== []) {
            return new JsonResponse(
                ['error' => 'Método no permitido.'],
                405,
                ['Allow' => implode(', ', array_unique($allowedMethods))],
            );
        }

        return new JsonResponse(['error' => 'Ruta no encontrada.'], 404);
    }

    /** @return array<string, string>|null */
    private function match(string $pattern, string $path): ?array
    {
        $parts = preg_split('/(\{[a-zA-Z_][a-zA-Z0-9_]*\})/', $pattern, -1, PREG_SPLIT_DELIM_CAPTURE);
        $parameterNames = [];
        $regex = '';

        foreach ($parts as $part) {
            if (preg_match('/^\{([a-zA-Z_][a-zA-Z0-9_]*)\}$/', $part, $matches) === 1) {
                $parameterNames[] = $matches[1];
                $regex .= '([^/]+)';
                continue;
            }

            $regex .= preg_quote($part, '#');
        }

        if (preg_match('#^' . $regex . '$#D', $path, $matches) !== 1) {
            return null;
        }

        $parameters = [];

        foreach ($parameterNames as $index => $name) {
            $parameters[$name] = rawurldecode($matches[$index + 1]);
        }

        return $parameters;
    }

    private function normalizePattern(string $pattern): string
    {
        $normalizedPattern = '/' . trim($pattern, '/');

        return $normalizedPattern === '/' ? $normalizedPattern : rtrim($normalizedPattern, '/');
    }
}
