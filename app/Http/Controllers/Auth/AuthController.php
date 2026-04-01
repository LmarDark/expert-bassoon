<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\Auth\AuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

final class AuthController extends Controller
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $isAuthenticated = $this->authService->AutenticationFunction($request->validated());

        if ($isAuthenticated) {
            $request->session()->regenerate();

            $url = $request->input('return_to')
                           ?? session()->pull('url.intended')
                           ?? route('dashboard');

            $host = parse_url($url, PHP_URL_HOST);
            $isSafe = $host && str_ends_with($host, env('ALLOWED_HOST_REDIRECT'));
            $destination = $isSafe ? $url : route('dashboard');

            return redirect()->away($destination);
        }

        return back()->withErrors([
            'username' => 'Usuário ou senha incorretos.',
        ]);
    }

    public function logout(): RedirectResponse
    {
        Auth::logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login');
    }
}
