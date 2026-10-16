<?php

namespace App\Middleware;

use App\Core\Auth;
use App\Core\Middleware;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

/**
 * Allows POST /api/attendance/scan from either an active kiosk session
 * or a logged-in user with the attendance.kiosk permission.
 */
class ScanAuthMiddleware implements Middleware
{
    public function handle(Request $request): bool
    {
        if (Session::has('kiosk_device_id')) {
            return true;
        }

        if (Auth::check() && Auth::can('attendance.kiosk')) {
            return true;
        }

        Response::error('Akses scan tidak diizinkan.', 'unauthorized', 401);
        return false;
    }
}
