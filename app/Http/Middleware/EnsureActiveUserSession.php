<?php

namespace App\Http\Middleware;

use App\Services\UserSessionService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveUserSession
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && ! UserSessionService::isSessionActive($request)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->header('X-Inertia')) {
                return Inertia::location(route('login'));
            }

            return redirect()->route('login');
        }

        return $next($request);
    }
}
