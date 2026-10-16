<?php

use App\Controllers\IdCardController;
use App\Middleware\AdminMiddleware;
use App\Middleware\AuthMiddleware;

/** @var App\Core\Router $router */

$router->group(['prefix' => '/idcard', 'middleware' => [AuthMiddleware::class, AdminMiddleware::class]], function (App\Core\Router $router) {
    $router->get('', [IdCardController::class, 'bulkForm']);
    $router->get('/bulk', [IdCardController::class, 'bulkPrint']);
    $router->get('/students/{id}', [IdCardController::class, 'studentCard']);
    $router->get('/teachers/{id}', [IdCardController::class, 'teacherCard']);
});
