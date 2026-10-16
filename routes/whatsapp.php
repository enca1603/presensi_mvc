<?php

use App\Controllers\WhatsAppController;
use App\Middleware\AdminMiddleware;
use App\Middleware\AuthMiddleware;

/** @var App\Core\Router $router */

$router->group(['middleware' => [AuthMiddleware::class, AdminMiddleware::class]], function (App\Core\Router $router) {
    $router->get('/settings/whatsapp', [WhatsAppController::class, 'settings']);
    $router->post('/settings/whatsapp/update', [WhatsAppController::class, 'updateSettings']);

    $router->get('/whatsapp/send', [WhatsAppController::class, 'sendTestForm']);
    $router->post('/whatsapp/send-now', [WhatsAppController::class, 'sendTest']);
    $router->get('/whatsapp/logs', [WhatsAppController::class, 'logs']);
    $router->post('/whatsapp/logs/{id}/retry', [WhatsAppController::class, 'retry']);
});
