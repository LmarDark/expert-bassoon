<?php

declare(strict_types=1);

namespace App\Http\Controllers\Sso;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\App;
use App\Models\SsoToken;
use App\Services\Auth\JwtService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use RuntimeException;

final class TokenController extends Controller
{
    public function issue(Request $request): RedirectResponse
    {
        $app = App::query()
            ->where('api_key', $request->query('app'))
            ->where('active', true)
            ->first();

        if (! $app instanceof App) {
            abort(403, 'Aplicação não encontrada ou inativa.');
        }

        $redirect = (string) $request->query('redirect', $app->callback_url ?? '');

        if ($redirect === '') {
            abort(422, 'Parâmetro redirect é obrigatório.');
        }

        $host = parse_url($redirect, PHP_URL_HOST);

        if (! is_string($host) || ! $app->isAllowedDomain($host)) {
            abort(403, 'Domínio de redirecionamento não autorizado para esta aplicação.');
        }

        $user = $request->user();
        $jti = Str::random(32);

        SsoToken::query()->create([
            'jti' => $jti,
            'user_id' => $user->id,
            'app_id' => $app->id,
            'expires_at' => now()->addSeconds(120),
        ]);

        $token = JwtService::encode([
            'sub' => $user->username,
            'user_id' => $user->id,
            'nickname' => $user->nickname,
            'is_admin' => $user->is_admin,
            'app' => $app->name,
            'jti' => $jti,
            'iat' => now()->unix(),
            'exp' => now()->addSeconds(120)->unix(),
        ], $app->api_key);

        $separator = str_contains($redirect, '?') ? '&' : '?';

        return redirect()->away("{$redirect}{$separator}sso_token={$token}");
    }

    public function logout(Request $request): RedirectResponse
    {
        $actorId = Auth::id();
        $username = Auth::user()?->username;

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $redirect = (string) $request->query('redirect', '');
        $host = $redirect !== '' ? parse_url($redirect, PHP_URL_HOST) : null;

        $app = null;
        if ($request->query('app') !== null) {
            $app = App::query()
                ->where('api_key', $request->query('app'))
                ->where('active', true)
                ->first();
        }

        $isSafe = is_string($host) && $app instanceof App && $app->isAllowedDomain($host);
        $destination = $isSafe ? $redirect : route('login');

        ActivityLog::create([
            'actor_id' => $actorId,
            'event' => 'logout',
            'target_username' => $username,
            'ip_address' => $request->ip(),
        ]);

        return redirect()->away($destination);
    }

    public function validate(Request $request): JsonResponse
    {
        $request->validate([
            'token' => ['required', 'string'],
            'api_key' => ['required', 'string'],
        ]);

        $app = App::query()
            ->where('api_key', $request->input('api_key'))
            ->where('active', true)
            ->first();

        if (! $app instanceof App) {
            return response()->json(['valid' => false, 'error' => 'Aplicação não encontrada ou inativa.'], 401);
        }

        try {
            $payload = JwtService::decode((string) $request->input('token'), $app->api_key);
        } catch (RuntimeException $e) {
            return response()->json(['valid' => false, 'error' => 'Token inválido.'], 401);
        }

        if (! isset($payload['exp']) || ! is_int($payload['exp']) || $payload['exp'] < now()->unix()) {
            return response()->json(['valid' => false, 'error' => 'Token expirado.'], 401);
        }

        if (! isset($payload['jti']) || ! is_string($payload['jti'])) {
            return response()->json(['valid' => false, 'error' => 'Token inválido.'], 401);
        }

        $ssoToken = SsoToken::query()
            ->where('jti', $payload['jti'])
            ->where('app_id', $app->id)
            ->first();

        if (! $ssoToken instanceof SsoToken) {
            return response()->json(['valid' => false, 'error' => 'Token não encontrado.'], 401);
        }

        if ($ssoToken->isUsed()) {
            return response()->json(['valid' => false, 'error' => 'Token já utilizado.'], 401);
        }

        if ($ssoToken->isExpired()) {
            return response()->json(['valid' => false, 'error' => 'Token expirado.'], 401);
        }

        $ssoToken->used_at = Carbon::now();
        $ssoToken->save();

        return response()->json([
            'valid' => true,
            'user' => [
                'id' => $payload['user_id'] ?? null,
                'username' => $payload['sub'] ?? null,
                'nickname' => $payload['nickname'] ?? null,
                'is_admin' => $payload['is_admin'] ?? false,
            ],
        ]);
    }
}
