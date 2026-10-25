<?php

declare(strict_types=1);

use App\Http\Request;
use App\Infrastructure\ErrorHandler;

$projectRoot = __DIR__;

if (!is_file($projectRoot . '/vendor/autoload.php')) {
    $projectRoot = dirname($projectRoot);
}

require $projectRoot . '/vendor/autoload.php';

App\Infrastructure\Environment::load($projectRoot . '/.env');
ErrorHandler::register(App\Infrastructure\Environment::get('APP_DEBUG') === 'true');

/** @var App\Http\Router $router */
$router = require $projectRoot . '/bootstrap.php';
$router->dispatch(Request::fromGlobals())->send();
