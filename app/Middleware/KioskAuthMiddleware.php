<?php

namespace App\Middleware;

use App\Core\Middleware;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class KioskAuthMiddleware implements Middleware
{
    public function handle(Request $request): bool
    {
        if (!Session::has('kiosk_device_id')) {
            if (str_starts_with($request->uri(), '/api/')) {
                Response::error('Kiosk belum login.', 'kiosk_unauthorized', 401);
                return false;
            }

            Response::redirect('/kiosk/login');
            return false;
        }

        return true;
    }
}
