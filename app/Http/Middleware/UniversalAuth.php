<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

final class UniversalAuth
{
    private bool $isAuthenticated;

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $this->isAuthenticated = Auth::check();

        if(!$this->isAuthenticated) {
            return $this->isNotAuthenticated($request, $next, $this->isAuthenticated);
        }

        return $this->isAuthenticated($request, $next, $this->isAuthenticated);
    }

    private function isAuthenticated(Request $request, Closure $next, bool $isAuthenticated): Response
    {
        if($isAuthenticated && $request->routeIs('login')) {
            return redirect()->route('dashboard');
        }

        return $next($request);
    }

    private function isNotAuthenticated(Request $request, Closure $next, bool $isAuthenticated): Response
    {
        if (! $isAuthenticated && ! $request->routeIs('login')) {
            return redirect()->route('login', ['return_to' => $request->fullUrl()]);
        }

        return $next($request);
    }
}
