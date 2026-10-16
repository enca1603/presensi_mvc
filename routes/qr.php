<?php

use App\Controllers\QrController;
use App\Middleware\AdminMiddleware;
use App\Middleware\AuthMiddleware;

/** @var App\Core\Router $router */

$router->group(['prefix' => '/qr', 'middleware' => [AuthMiddleware::class, AdminMiddleware::class]], function (App\Core\Router $router) {
    $router->get('/students', [QrController::class, 'students']);
    $router->get('/students/print-bulk', [QrController::class, 'printBulkStudents']);
    $router->get('/students/{id}/print', [QrController::class, 'printStudent']);
    $router->post('/students/{id}/regenerate', [QrController::class, 'regenerateStudent']);

    $router->get('/teachers', [QrController::class, 'teachers']);
    $router->get('/teachers/{id}/print', [QrController::class, 'printTeacher']);
    $router->post('/teachers/{id}/regenerate', [QrController::class, 'regenerateTeacher']);
});
