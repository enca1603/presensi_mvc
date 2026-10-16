<?php

namespace App\Middleware;

class SuperAdminMiddleware extends RoleMiddleware
{
    public function __construct()
    {
        parent::__construct(['super_admin']);
    }
}
