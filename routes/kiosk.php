<?php

use App\Controllers\KioskController;
use App\Controllers\KioskDeviceController;
use App\Middleware\AdminMiddleware;
use App\Middleware\AuthMiddleware;
use App\Middleware\KioskAuthMiddleware;

/** @var App\Core\Router $router */

$router->get('/kiosk/login', [KioskController::class, 'showLogin']);
$router->post('/kiosk/login', [KioskController::class, 'login']);
$router->get('/kiosk', [KioskController::class, 'show'], [KioskAuthMiddleware::class]);
$router->post('/kiosk/verify-exit-pin', [KioskController::class, 'verifyExitPin'], [KioskAuthMiddleware::class]);
$router->get('/kiosk/logout', [KioskController::class, 'logout'], [KioskAuthMiddleware::class]);

$router->group(['prefix' => '/settings/kiosks', 'middleware' => [AuthMiddleware::class, AdminMiddleware::class]], function (App\Core\Router $router) {
    $router->get('', [KioskDeviceController::class, 'index']);
    $router->get('/create', [KioskDeviceController::class, 'create']);
    $router->post('/store', [KioskDeviceController::class, 'store']);
    $router->get('/{id}/edit', [KioskDeviceController::class, 'edit']);
    $router->post('/{id}/update', [KioskDeviceController::class, 'update']);
    $router->post('/{id}/delete', [KioskDeviceController::class, 'destroy']);
});
