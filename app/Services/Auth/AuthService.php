<?php

declare(strict_types=1);

namespace App\Services\Auth;

use Illuminate\Support\Facades\Auth;

final class AuthService
{
    /**
     * @param  array<string, mixed>  $credentials
     */
    public function AutenticationFunction(array $credentials): bool
    {
        if (Auth::attempt([
            'username' => $credentials['username'],
            'password' => $credentials['password'],
        ], (bool) ($credentials['remember'] ?? false))) {
            return true;
        }

        return false;
    }
}
