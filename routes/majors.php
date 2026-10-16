<?php

use App\Controllers\MajorController;
use App\Middleware\AdminMiddleware;
use App\Middleware\AuthMiddleware;

/** @var App\Core\Router $router */

$router->group(['prefix' => '/majors', 'middleware' => [AuthMiddleware::class, AdminMiddleware::class]], function (App\Core\Router $router) {
    $router->get('', [MajorController::class, 'index']);
    $router->get('/create', [MajorController::class, 'create']);
    $router->post('/store', [MajorController::class, 'store']);
    $router->get('/{id}/edit', [MajorController::class, 'edit']);
    $router->post('/{id}/update', [MajorController::class, 'update']);
    $router->post('/{id}/delete', [MajorController::class, 'destroy']);
});
