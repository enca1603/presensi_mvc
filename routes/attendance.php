<?php

use App\Controllers\AttendanceController;
use App\Middleware\AdminMiddleware;
use App\Middleware\AuthMiddleware;

/** @var App\Core\Router $router */

$router->group(['prefix' => '/attendance', 'middleware' => [AuthMiddleware::class, AdminMiddleware::class]], function (App\Core\Router $router) {
    $router->get('/students', [AttendanceController::class, 'students']);
    $router->get('/teachers', [AttendanceController::class, 'teachers']);
    $router->get('/manual', [AttendanceController::class, 'manualForm']);
    $router->post('/manual/store', [AttendanceController::class, 'manualStore']);
    $router->get('/corrections', [AttendanceController::class, 'corrections']);
    $router->get('/corrections/{id}/edit', [AttendanceController::class, 'correctionForm']);
    $router->post('/corrections/{id}/update', [AttendanceController::class, 'correctionStore']);
});
