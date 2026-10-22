<?php

declare(strict_types=1);

use App\Controller\ReservaApiController;
use App\Controller\ReservaPageController;
use App\Http\Router;
use App\Infrastructure\ConnectionFactory;
use App\Infrastructure\Environment;
use App\Repository\PdoReservaEventoRepository;
use App\Repository\PdoReservaRepository;
use App\Service\ReservaService;
use App\Validation\CrearReservaValidator;
use Twig\Environment as TwigEnvironment;
use Twig\Loader\FilesystemLoader;

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
$twig = new TwigEnvironment(new FilesystemLoader(__DIR__ . '/templates'), ['autoescape' => 'html']);
$pageController = new ReservaPageController($service, $twig);
$router = new Router();
$router->get('/', [$pageController, 'listado']);
$router->get('/reservas/{id}', [$pageController, 'detalle']);
$router->get('/api/reservas', [$controller, 'listar']);
$router->get('/api/reservas/{id}', [$controller, 'detalle']);
$router->post('/api/reservas', [$controller, 'crear']);
$router->patch('/api/reservas/{id}/estado', [$controller, 'cambiarEstado']);

return $router;
