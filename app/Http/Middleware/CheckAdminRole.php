<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminRole
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $routeName = $request->route()?->getName();

        if (!$user || !$user->role_id) {
            abort(403, 'Bạn chưa được cấp quyền truy cập khu vực quản trị.');
        }

        if ($user->role === 'customer') {
            abort(403, 'Khu vực này chỉ dành cho nhân sự khách sạn.');
        }

        if ($user->isAdmin()) {
            return $next($request);
        }

        $permissions = (array) (optional($user->roleRelation)->permissions ?? []);

        $requiredPermission = $this->resolveRequiredPermission($routeName);

        if ($requiredPermission === null) {
            abort(403, 'Tính năng này chưa được cấu hình quyền truy cập.');
        }

        if (in_array($requiredPermission, $permissions, true)) {
            return $next($request);
        }

        abort(403, 'Bạn không có quyền truy cập chức năng này.');
    }

    private function resolveRequiredPermission(?string $routeName): ?string
    {
        if (!$routeName) {
            return null;
        }

        $routePermissions = config('admin_permissions.route_permissions', []);

        return $routePermissions[$routeName] ?? null;
    }
}
