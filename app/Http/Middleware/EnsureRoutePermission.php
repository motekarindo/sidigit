<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRoutePermission
{
    public function handle(Request $request, Closure $next): Response
    {
        $routeName = $request->route()?->getName();
        if (! filled($routeName)) {
            return $next($request);
        }

        $permission = $this->resolvePermission($routeName);
        if (! filled($permission)) {
            return $next($request);
        }

        $user = $request->user();
        if (! $user || ! $user->can($permission)) {
            abort(403);
        }

        return $next($request);
    }

    protected function resolvePermission(string $routeName): ?string
    {
        $explicit = config('route_permissions.explicit', []);
        if (array_key_exists($routeName, $explicit)) {
            return $explicit[$routeName];
        }

        $parts = explode('.', $routeName);
        if (count($parts) !== 2) {
            return null;
        }

        [$resource, $action] = $parts;
        $resourcePrefixes = config('route_permissions.resource_prefixes', []);
        $resourcePrefix = $resourcePrefixes[$resource] ?? null;
        if (! filled($resourcePrefix)) {
            return null;
        }

        $actionMap = config('route_permissions.resource_actions', []);
        $permissionAction = $actionMap[$action] ?? null;
        if (! filled($permissionAction)) {
            return null;
        }

        return $resourcePrefix . '.' . $permissionAction;
    }
}
