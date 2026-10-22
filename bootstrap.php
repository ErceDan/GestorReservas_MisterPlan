<?php

declare(strict_types=1);

use App\Controller\ReservaApiController;
use App\Http\Router;
use App\Infrastructure\ConnectionFactory;
use App\Infrastructure\Environment;
use App\Repository\PdoReservaEventoRepository;
use App\Repository\PdoReservaRepository;
use App\Service\ReservaService;
use App\Validation\CrearReservaValidator;

Environment::load(__DIR__ . '/.env');

$connection = ConnectionFactory::create(
    Environment::required('DB_HOST'),
    (int) Environment::required('DB_PORT'),
    Environment::required('DB_DATABASE'),
    Environment::required('DB_USERNAME'),
    getenv('DB_PASSWORD') ?: '',
);
$service = new ReservaService(
    new PdoReservaRepository($connection),
    new PdoReservaEventoRepository($connection),
    new CrearReservaValidator(),
    $connection,
);
$controller = new ReservaApiController($service);
$router = new Router();
$router->get('/api/reservas', [$controller, 'listar']);
$router->get('/api/reservas/{id}', [$controller, 'detalle']);
$router->post('/api/reservas', [$controller, 'crear']);
$router->patch('/api/reservas/{id}/estado', [$controller, 'cambiarEstado']);

return $router;
