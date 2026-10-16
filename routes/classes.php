<?php

use App\Controllers\ClassController;
use App\Middleware\AdminMiddleware;
use App\Middleware\AuthMiddleware;

/** @var App\Core\Router $router */

$router->group(['prefix' => '/classes', 'middleware' => [AuthMiddleware::class, AdminMiddleware::class]], function (App\Core\Router $router) {
    $router->get('', [ClassController::class, 'index']);
    $router->get('/create', [ClassController::class, 'create']);
    $router->post('/store', [ClassController::class, 'store']);
    $router->get('/{id}/edit', [ClassController::class, 'edit']);
    $router->post('/{id}/update', [ClassController::class, 'update']);
    $router->post('/{id}/delete', [ClassController::class, 'destroy']);
});
