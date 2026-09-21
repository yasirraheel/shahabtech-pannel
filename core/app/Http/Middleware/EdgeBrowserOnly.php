<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EdgeBrowserOnly
{
    /**
     * Handle an incoming request.
     * Restricts portal access exclusively to Microsoft Edge browser (excluding Admin panel).
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Always allow Admin panel requests (web/login/dashboard/all admin routes)
        if ($request->is('admin*') || $request->routeIs('admin.*') || str_starts_with($request->path(), 'admin')) {
            return $next($request);
        }

        // 2. Always allow essential background APIs, webhooks, cron, and assets
        if (
            $request->is('api*') ||
            $request->is('ipn*') ||
            $request->is('cron*') ||
            $request->is('cron') ||
            $request->is('clear') ||
            $request->is('extension/download*') ||
            $request->is('placeholder-image*') ||
            $request->is('maintenance-mode*')
        ) {
            return $next($request);
        }

        // 3. Allow programmatic JSON / AJAX requests from extensions or backend tools
        if ($request->expectsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest' || $request->header('X-Admin-Key')) {
            return $next($request);
        }

        // 4. Detect Microsoft Edge browser
        $userAgent = $request->header('User-Agent', '');
        $secChUa = $request->header('sec-ch-ua', '');

        $isEdge = str_contains($userAgent, 'Edg/')
            || str_contains($userAgent, 'Edge/')
            || str_contains($userAgent, 'EdgA/')
            || str_contains($userAgent, 'EdgiOS/')
            || stripos($secChUa, 'Microsoft Edge') !== false
            || stripos($secChUa, '"Edge"') !== false
            || stripos($secChUa, 'Edge;') !== false;

        $isEdgeOnlyPage = $request->is('edge-only') || $request->routeIs('edge.only');

        // If user is on Edge and navigates to the block page, send them to homepage
        if ($isEdge && $isEdgeOnlyPage) {
            return redirect()->route('home');
        }

        // If user is NOT on Edge and tries to visit any regular user/public page, redirect to the block page
        if (!$isEdge) {
            if ($isEdgeOnlyPage) {
                return $next($request);
            }
            return redirect()->route('edge.only');
        }

        return $next($request);
    }
}
