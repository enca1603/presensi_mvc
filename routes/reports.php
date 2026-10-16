<?php

use App\Controllers\ReportController;
use App\Middleware\AdminMiddleware;
use App\Middleware\AuthMiddleware;

/** @var App\Core\Router $router */

$router->group(['prefix' => '/reports', 'middleware' => [AuthMiddleware::class, AdminMiddleware::class]], function (App\Core\Router $router) {
    $router->get('/daily', [ReportController::class, 'daily']);
    $router->get('/daily/export', [ReportController::class, 'dailyExport']);

    $router->get('/monthly', [ReportController::class, 'monthly']);
    $router->get('/monthly/export', [ReportController::class, 'monthlyExport']);

    $router->get('/students', [ReportController::class, 'students']);
    $router->get('/students/{id}', [ReportController::class, 'studentDetail']);
    $router->get('/students/{id}/export', [ReportController::class, 'studentExport']);

    $router->get('/teachers', [ReportController::class, 'teachers']);
    $router->get('/teachers/{id}', [ReportController::class, 'teacherDetail']);
    $router->get('/teachers/{id}/export', [ReportController::class, 'teacherExport']);

    $router->get('/classes', [ReportController::class, 'classes']);
    $router->get('/classes/{id}', [ReportController::class, 'classDetail']);
    $router->get('/classes/{id}/export', [ReportController::class, 'classExport']);
});
