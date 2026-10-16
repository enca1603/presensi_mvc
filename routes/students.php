<?php

use App\Controllers\StudentController;
use App\Middleware\AdminMiddleware;
use App\Middleware\AuthMiddleware;

/** @var App\Core\Router $router */

$router->group(['prefix' => '/students', 'middleware' => [AuthMiddleware::class, AdminMiddleware::class]], function (App\Core\Router $router) {
    $router->get('', [StudentController::class, 'index']);
    $router->get('/create', [StudentController::class, 'create']);
    $router->post('/store', [StudentController::class, 'store']);
    $router->get('/{id}', [StudentController::class, 'show']);
    $router->get('/{id}/edit', [StudentController::class, 'edit']);
    $router->post('/{id}/update', [StudentController::class, 'update']);
    $router->post('/{id}/delete', [StudentController::class, 'destroy']);
    $router->get('/{id}/qr-image', [StudentController::class, 'qrImage']);
    $router->post('/{id}/regenerate-qr', [StudentController::class, 'regenerateQr']);
});
