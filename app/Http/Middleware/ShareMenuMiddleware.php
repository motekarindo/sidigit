<?php

namespace App\Http\Middleware;

use App\Services\MenuCacheService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ShareMenuMiddleware
{
    protected MenuCacheService $service;

    public function __construct(MenuCacheService $service)
    {
        $this->service = $service;
    }

    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            view()->share('sidebarMenus', $this->service->sidebarForUser(auth()->user()));
        }

        return $next($request);
    }
}
