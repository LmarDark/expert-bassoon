<?php

declare(strict_types=1);

use App\Models\App;
use App\Models\SsoToken;
use App\Models\User;
use App\Services\Auth\JwtService;
use Illuminate\Support\Str;

// IMPORTANT: use $this->clientApp, never $this->app — the latter is the Laravel container
beforeEach(function () {
    $this->user      = User::factory()->create();
    $this->clientApp = App::factory()->create([
        'allowed_domains' => ['meuapp.com'],
        'callback_url'    => 'https://meuapp.com/callback',
    ]);
});

describe('TokenController', function () {
    describe('issue (GET /sso/token)', function () {
        it('redirects with sso_token when authenticated', function () {
            $response = $this->actingAs($this->user)
                ->get(route('sso.token', [
                    'app'      => $this->clientApp->api_key,
                    'redirect' => 'https://meuapp.com/callback',
                ]));

            $response->assertRedirectContains('sso_token=');
            expect(SsoToken::query()->where('user_id', $this->user->id)->exists())->toBeTrue();
        });

        it('requires authentication', function () {
            $this->get(route('sso.token', [
                'app'      => $this->clientApp->api_key,
                'redirect' => 'https://meuapp.com/callback',
            ]))->assertRedirectContains(route('login'));
        });

        it('returns 403 for unknown api_key', function () {
            $this->actingAs($this->user)
                ->get(route('sso.token', [
                    'app'      => 'invalid-key',
                    'redirect' => 'https://meuapp.com/callback',
                ]))
                ->assertForbidden();
        });

        it('returns 403 for inactive app', function () {
            $inactive = App::factory()->inactive()->create(['allowed_domains' => ['meuapp.com']]);

            $this->actingAs($this->user)
                ->get(route('sso.token', [
                    'app'      => $inactive->api_key,
                    'redirect' => 'https://meuapp.com/callback',
                ]))
                ->assertForbidden();
        });

        it('returns 403 for disallowed redirect domain', function () {
            $this->actingAs($this->user)
                ->get(route('sso.token', [
                    'app'      => $this->clientApp->api_key,
                    'redirect' => 'https://outrodominio.com/callback',
                ]))
                ->assertForbidden();
        });

        it('uses callback_url when redirect param is omitted', function () {
            $response = $this->actingAs($this->user)
                ->get(route('sso.token', ['app' => $this->clientApp->api_key]));

            $response->assertRedirectContains('meuapp.com/callback');
            $response->assertRedirectContains('sso_token=');
        });

        it('the jwt payload contains user info', function () {
            $response = $this->actingAs($this->user)
                ->get(route('sso.token', [
                    'app'      => $this->clientApp->api_key,
                    'redirect' => 'https://meuapp.com/callback',
                ]));

            $location = $response->headers->get('Location');
            parse_str((string) parse_url((string) $location, PHP_URL_QUERY), $params);
            $payload = JwtService::decode((string) $params['sso_token'], $this->clientApp->api_key);

            expect($payload['sub'])->toBe($this->user->username);
            expect($payload['user_id'])->toBe($this->user->id);
            expect($payload['is_admin'])->toBeFalse();
        });
    });

    describe('validate (POST /sso/validate)', function () {
        it('validates a valid token and marks it as used', function () {
            $jti   = Str::random(32);
            $token = JwtService::encode([
                'sub'      => $this->user->username,
                'user_id'  => $this->user->id,
                'nickname' => null,
                'is_admin' => false,
                'jti'      => $jti,
                'iat'      => now()->unix(),
                'exp'      => now()->addMinutes(2)->unix(),
            ], $this->clientApp->api_key);

            SsoToken::factory()->create([
                'jti'     => $jti,
                'user_id' => $this->user->id,
                'app_id'  => $this->clientApp->id,
            ]);

            $response = $this->postJson(route('sso.validate'), [
                'token'   => $token,
                'api_key' => $this->clientApp->api_key,
            ]);

            $response->assertOk()->assertJson(['valid' => true]);
            expect(SsoToken::query()->where('jti', $jti)->first()?->used_at)->not->toBeNull();
        });

        it('rejects token with invalid signature', function () {
            $jti   = Str::random(32);
            $token = JwtService::encode(['jti' => $jti, 'exp' => now()->addMinutes(2)->unix()], 'wrong-secret');

            SsoToken::factory()->create([
                'jti'     => $jti,
                'user_id' => $this->user->id,
                'app_id'  => $this->clientApp->id,
            ]);

            $this->postJson(route('sso.validate'), ['token' => $token, 'api_key' => $this->clientApp->api_key])
                ->assertUnauthorized()
                ->assertJson(['valid' => false]);
        });

        it('rejects expired token', function () {
            $jti   = Str::random(32);
            $token = JwtService::encode(['jti' => $jti, 'exp' => now()->subMinute()->unix()], $this->clientApp->api_key);

            SsoToken::factory()->create([
                'jti'     => $jti,
                'user_id' => $this->user->id,
                'app_id'  => $this->clientApp->id,
            ]);

            $this->postJson(route('sso.validate'), ['token' => $token, 'api_key' => $this->clientApp->api_key])
                ->assertUnauthorized()
                ->assertJson(['valid' => false]);
        });

        it('rejects already used token', function () {
            $jti   = Str::random(32);
            $token = JwtService::encode(['jti' => $jti, 'exp' => now()->addMinutes(2)->unix()], $this->clientApp->api_key);

            SsoToken::factory()->used()->create([
                'jti'     => $jti,
                'user_id' => $this->user->id,
                'app_id'  => $this->clientApp->id,
            ]);

            $this->postJson(route('sso.validate'), ['token' => $token, 'api_key' => $this->clientApp->api_key])
                ->assertUnauthorized()
                ->assertJson(['valid' => false]);
        });

        it('rejects unknown api_key', function () {
            $this->postJson(route('sso.validate'), ['token' => 'any', 'api_key' => 'unknown'])
                ->assertUnauthorized()
                ->assertJson(['valid' => false]);
        });

        it('requires token and api_key fields', function () {
            $this->postJson(route('sso.validate'), [])
                ->assertUnprocessable();
        });
    });

    describe('logout (GET /sso/logout)', function () {
        it('logs out and redirects to safe redirect', function () {
            $this->actingAs($this->user)
                ->get(route('sso.logout', [
                    'app'      => $this->clientApp->api_key,
                    'redirect' => 'https://meuapp.com/logged-out',
                ]))
                ->assertRedirect('https://meuapp.com/logged-out');

            $this->assertGuest();
        });

        it('redirects to login when redirect domain is not allowed', function () {
            $this->actingAs($this->user)
                ->get(route('sso.logout', [
                    'app'      => $this->clientApp->api_key,
                    'redirect' => 'https://hacker.com/steal',
                ]))
                ->assertRedirect(route('login'));
        });

        it('redirects to login when no redirect param', function () {
            $this->actingAs($this->user)
                ->get(route('sso.logout'))
                ->assertRedirect(route('login'));
        });

        it('works even when not authenticated', function () {
            $this->get(route('sso.logout'))
                ->assertRedirect(route('login'));
        });
    });
});
