<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class CheckFirstSetup
{
    /**
     * Redirect to setup page when no users exist in the database.
     * Redirect away from setup page when users already exist.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $hasUsers = User::query()->exists();

        if (! $hasUsers && ! $request->routeIs('setup', 'setup.store')) {
            return redirect()->route('setup');
        }

        if ($hasUsers && $request->routeIs('setup', 'setup.store')) {
            return redirect()->route('login');
        }

        return $next($request);
    }
}
