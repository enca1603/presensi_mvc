<?php

use App\Controllers\TeacherController;
use App\Middleware\AdminMiddleware;
use App\Middleware\AuthMiddleware;

/** @var App\Core\Router $router */

$router->group(['prefix' => '/teachers', 'middleware' => [AuthMiddleware::class, AdminMiddleware::class]], function (App\Core\Router $router) {
    $router->get('', [TeacherController::class, 'index']);
    $router->get('/create', [TeacherController::class, 'create']);
    $router->post('/store', [TeacherController::class, 'store']);
    $router->get('/{id}', [TeacherController::class, 'show']);
    $router->get('/{id}/edit', [TeacherController::class, 'edit']);
    $router->post('/{id}/update', [TeacherController::class, 'update']);
    $router->post('/{id}/delete', [TeacherController::class, 'destroy']);
    $router->get('/{id}/qr-image', [TeacherController::class, 'qrImage']);
    $router->post('/{id}/regenerate-qr', [TeacherController::class, 'regenerateQr']);
});
