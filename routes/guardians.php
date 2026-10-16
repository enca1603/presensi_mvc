<?php

use App\Controllers\GuardianController;
use App\Middleware\AdminMiddleware;
use App\Middleware\AuthMiddleware;

/** @var App\Core\Router $router */

$router->group(['prefix' => '/guardians', 'middleware' => [AuthMiddleware::class, AdminMiddleware::class]], function (App\Core\Router $router) {
    $router->get('', [GuardianController::class, 'index']);
    $router->get('/create', [GuardianController::class, 'create']);
    $router->post('/store', [GuardianController::class, 'store']);
    $router->get('/{id}/edit', [GuardianController::class, 'edit']);
    $router->post('/{id}/update', [GuardianController::class, 'update']);
    $router->post('/{id}/delete', [GuardianController::class, 'destroy']);
});
