<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Facades\Activity;
use Symfony\Component\HttpFoundation\Response;

class LogActivity
{
    /**
     * Handle an incoming request.
     *
     * Records page-level events (login, logout, register, page views) via the
     * Spatie activity log facade. Model CRUD events are captured by the
     * `LogsActivity` traits on the Transaction and Category models.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $authenticatedUser = $request->user();
        $response = $next($request);

        // Don't log API requests or health checks
        if ($request->is('api/*') || $request->is('_health')) {
            return $response;
        }

        // Only log successful responses
        if (! $response->isOk() && ! $response->isRedirect()) {
            return $response;
        }

        // Login may establish the user during the request, while logout clears it.
        // Prefer the current authenticated user, then retain the request-start user.
        $user = Auth::user() ?? $authenticatedUser;
        $action = $this->detectAction($request);

        if (! $action) {
            return $response;
        }

        // Build description
        $description = $this->buildDescription($action, $request);

        // Log a page-level event via Spatie. The current user is attached as the
        // causer by the `beforeLogging` hook registered in AppServiceProvider.
        Activity::causedBy($user)
            ->event($action)
            ->withProperties([
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'route' => $request->route()?->getName(),
                'method' => $request->method(),
                'path' => $request->path(),
            ])
            ->log($description);

        return $response;
    }

    private function detectAction(Request $request): ?string
    {
        $route = $request->route()?->getName() ?? '';
        $method = $request->method();
        $path = $request->path();

        // Authentication events
        if (str_contains($route, 'login') || $path === 'login') {
            return $method === 'POST' ? 'login' : 'view_login';
        }
        if (str_contains($route, 'logout')) {
            return 'logout';
        }
        if (str_contains($route, 'register') && $method === 'POST') {
            return 'register';
        }

        // Report operations
        if (str_contains($route, 'reports')) {
            return 'view_report';
        }

        // Page views
        if ($path === 'dashboard') {
            return 'view_dashboard';
        }

        return null;
    }

    private function buildDescription(string $action, Request $request): ?string
    {
        return match ($action) {
            'login' => 'User logged in successfully',
            'logout' => 'User logged out',
            'register' => 'New user registration',
            'view_login' => 'Login page viewed',
            'view_report' => 'Report viewed',
            'view_dashboard' => 'Dashboard viewed',
            default => "Action: {$action}",
        };
    }
}
