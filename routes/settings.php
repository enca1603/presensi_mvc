<?php

use App\Controllers\RoleController;
use App\Controllers\SettingsController;
use App\Controllers\UserController;
use App\Middleware\AdminMiddleware;
use App\Middleware\AuthMiddleware;
use App\Middleware\SuperAdminMiddleware;

/** @var App\Core\Router $router */

$router->group(['prefix' => '/settings', 'middleware' => [AuthMiddleware::class, AdminMiddleware::class]], function (App\Core\Router $router) {
    $router->get('/school', [SettingsController::class, 'school']);
    $router->post('/school/update', [SettingsController::class, 'updateSchool']);

    $router->get('/attendance-hours', [SettingsController::class, 'attendanceHours']);
    $router->post('/attendance-hours/update', [SettingsController::class, 'updateAttendanceHours']);

    $router->get('/school-days', [SettingsController::class, 'schoolDays']);
    $router->post('/school-days/update', [SettingsController::class, 'updateSchoolDays']);

    $router->get('/audit-log', [SettingsController::class, 'auditLog']);
});

$router->group(['prefix' => '/settings', 'middleware' => [AuthMiddleware::class, SuperAdminMiddleware::class]], function (App\Core\Router $router) {
    $router->get('/users', [UserController::class, 'index']);
    $router->get('/users/create', [UserController::class, 'create']);
    $router->post('/users/store', [UserController::class, 'store']);
    $router->get('/users/{id}/edit', [UserController::class, 'edit']);
    $router->post('/users/{id}/update', [UserController::class, 'update']);

    $router->get('/roles', [RoleController::class, 'index']);
    $router->post('/roles/{id}/update', [RoleController::class, 'update']);
});
