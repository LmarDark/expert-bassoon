<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Middleware;

final class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /** @return array<string, mixed> */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $request->user() ? [
                    'nickname' => $request->user()->nickname,
                    'username' => $request->user()->username,
                    'is_admin' => $request->user()->is_admin,
                ] : null,
            ],
            'flash' => fn () => [
                'success' => $request->session()->get('success'),
            ],
            'settings' => fn () => [
                'login' => rescue(
                    fn () => Setting::loginSettings(),
                    ['app_name' => 'Sistema de Autenticação', 'show_logo' => true, 'primary_color' => '#F53003', 'custom_css' => '', 'logo_url' => null, 'bg_color' => ''],
                ),
            ],
        ];
    }
}
