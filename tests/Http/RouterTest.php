<?php

declare(strict_types=1);

namespace Tests\Http;

use App\Http\JsonResponse;
use App\Http\Request;
use App\Http\Router;
use PHPUnit\Framework\TestCase;

final class RouterTest extends TestCase
{
    public function testItDispatchesRouteParameters(): void
    {
        $router = new Router();
        $router->get('/api/reservas/{id}', static fn (Request $request, array $parameters): JsonResponse => new JsonResponse($parameters));

        $response = $router->dispatch(new Request('GET', '/api/reservas/42'));

        self::assertSame(200, $response->statusCode());
        self::assertSame('{"id":"42"}', $response->body());
    }

    public function testItReturnsMethodNotAllowedWithAllowedMethods(): void
    {
        $router = new Router();
        $router->get('/api/reservas', static fn (Request $request, array $parameters): JsonResponse => new JsonResponse([]));

        $response = $router->dispatch(new Request('POST', '/api/reservas'));

        self::assertSame(405, $response->statusCode());
        self::assertSame('GET', $response->headers()['Allow']);
    }
}
