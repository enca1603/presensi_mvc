<?php

namespace App\Middleware;

use App\Core\Auth;
use App\Core\Middleware;
use App\Core\Request;
use App\Core\Response;

/**
 * Restricts access to super_admin and admin_tu roles.
 * Use PermissionMiddleware subclasses or Auth::can() inside controllers
 * for finer-grained permission checks.
 */
class RoleMiddleware implements Middleware
{
    private array $allowedRoles;

    public function __construct(array $allowedRoles = ['super_admin', 'admin_tu'])
    {
        $this->allowedRoles = $allowedRoles;
    }

    public function handle(Request $request): bool
    {
        if (!Auth::hasRole($this->allowedRoles)) {
            if (str_starts_with($request->uri(), '/api/')) {
                Response::error('Anda tidak memiliki akses.', 'forbidden', 403);
                return false;
            }

            Response::forbidden();
            return false;
        }

        return true;
    }
}
