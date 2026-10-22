<?php

declare(strict_types=1);

use App\Http\Request;
use App\Infrastructure\ErrorHandler;

$projectRoot = is_file(dirname(__DIR__) . '/vendor/autoload.php') ? dirname(__DIR__) : __DIR__;

require $projectRoot . '/vendor/autoload.php';

ErrorHandler::register();

/** @var App\Http\Router $router */
$router = require $projectRoot . '/bootstrap.php';
$router->dispatch(Request::fromGlobals())->send();
