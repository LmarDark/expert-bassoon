<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

final class UniversalAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $isAuthenticated = Auth::check() || $request->hasCookie(Auth::getRecallerName());

        if (! $isAuthenticated) {
            if (! $request->routeIs('login')) {
                $returnTo = $request->fullUrl();

                return redirect()->route('login', ['return_to' => $returnTo]);
            }

            return $next($request);
        }

        if ($request->routeIs('login')) {
            return redirect()->route('dashboard');
        }

        return $next($request);
    }
}
