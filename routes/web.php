<?php

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\ProfileController;
use App\Middleware\AuthMiddleware;
use App\Middleware\AdminMiddleware;
use App\Middleware\GuestMiddleware;

/** @var App\Core\Router $router */

$router->get('/', [DashboardController::class, 'index'], [AuthMiddleware::class]);

$router->get('/login', [AuthController::class, 'showLogin'], [GuestMiddleware::class]);
$router->post('/login', [AuthController::class, 'login'], [GuestMiddleware::class]);
$router->post('/logout', [AuthController::class, 'logout'], [AuthMiddleware::class]);

$router->get('/dashboard', [DashboardController::class, 'index'], [AuthMiddleware::class]);

$router->get('/profile', [ProfileController::class, 'show'], [AuthMiddleware::class]);
$router->get('/profile/qr-image', [ProfileController::class, 'qrImage'], [AuthMiddleware::class]);
$router->post('/profile/change-password', [ProfileController::class, 'changePassword'], [AuthMiddleware::class]);

require __DIR__ . '/students.php';
require __DIR__ . '/teachers.php';
require __DIR__ . '/guardians.php';
require __DIR__ . '/classes.php';
require __DIR__ . '/majors.php';
require __DIR__ . '/academic_years.php';
require __DIR__ . '/qr.php';
require __DIR__ . '/attendance.php';
require __DIR__ . '/kiosk.php';
require __DIR__ . '/whatsapp.php';
require __DIR__ . '/settings.php';
require __DIR__ . '/reports.php';
require __DIR__ . '/idcard.php';
