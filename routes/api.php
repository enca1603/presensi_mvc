<?php

use App\Controllers\Api\AttendanceApiController;
use App\Controllers\Api\KioskApiController;
use App\Controllers\DashboardController;
use App\Middleware\AdminMiddleware;
use App\Middleware\AuthMiddleware;
use App\Middleware\ScanAuthMiddleware;

/** @var App\Core\Router $router */

$router->get('/api/health', function () {
    \App\Core\Response::success(['status' => 'online', 'time' => date('c')], 'Server online');
});

$router->post('/api/attendance/scan', [AttendanceApiController::class, 'scan'], [ScanAuthMiddleware::class]);

$router->get('/api/kiosk/stats', [KioskApiController::class, 'stats'], [ScanAuthMiddleware::class]);

$router->get('/api/dashboard/stats', [DashboardController::class, 'stats'], [AuthMiddleware::class, AdminMiddleware::class]);
