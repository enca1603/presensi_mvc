<?php

use App\Controllers\AcademicYearController;
use App\Middleware\AdminMiddleware;
use App\Middleware\AuthMiddleware;

/** @var App\Core\Router $router */

$router->group(['prefix' => '/academic-years', 'middleware' => [AuthMiddleware::class, AdminMiddleware::class]], function (App\Core\Router $router) {
    $router->get('', [AcademicYearController::class, 'index']);
    $router->get('/create', [AcademicYearController::class, 'create']);
    $router->post('/store', [AcademicYearController::class, 'store']);
    $router->get('/{id}/edit', [AcademicYearController::class, 'edit']);
    $router->post('/{id}/update', [AcademicYearController::class, 'update']);
    $router->post('/{id}/delete', [AcademicYearController::class, 'destroy']);
});
