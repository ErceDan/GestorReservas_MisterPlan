<?php

declare(strict_types=1);

use App\Http\Request;
use App\Infrastructure\ErrorHandler;

require dirname(__DIR__) . '/vendor/autoload.php';

ErrorHandler::register();

/** @var App\Http\Router $router */
$router = require dirname(__DIR__) . '/bootstrap.php';
$router->dispatch(Request::fromGlobals())->send();
