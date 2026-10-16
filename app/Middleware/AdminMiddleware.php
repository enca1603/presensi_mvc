<?php

namespace App\Middleware;

class AdminMiddleware extends RoleMiddleware
{
    public function __construct()
    {
        parent::__construct(['super_admin', 'admin_tu']);
    }
}
