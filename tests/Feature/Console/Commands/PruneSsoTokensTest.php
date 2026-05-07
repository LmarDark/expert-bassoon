<?php

declare(strict_types=1);

use App\Models\App;
use App\Models\SsoToken;
use App\Models\User;
use Illuminate\Support\Carbon;

beforeEach(function () {
    $this->user   = User::factory()->create();
    $this->ssoApp = App::factory()->create();
});

describe('sso:prune-tokens', function () {
    it('deletes tokens expired more than 24 hours ago by default', function () {
        SsoToken::create([
            'jti'        => 'old-token',
            'user_id'    => $this->user->id,
            'app_id'     => $this->ssoApp->id,
            'expires_at' => Carbon::now()->subHours(25),
            'used_at'    => null,
        ]);

        SsoToken::create([
            'jti'        => 'recent-expired-token',
            'user_id'    => $this->user->id,
            'app_id'     => $this->ssoApp->id,
            'expires_at' => Carbon::now()->subMinutes(30),
            'used_at'    => null,
        ]);

        $this->artisan('sso:prune-tokens')->assertSuccessful();

        expect(SsoToken::query()->where('jti', 'old-token')->exists())->toBeFalse();
        expect(SsoToken::query()->where('jti', 'recent-expired-token')->exists())->toBeTrue();
    });

    it('respects the --hours option', function () {
        SsoToken::create([
            'jti'        => 'two-hour-old-token',
            'user_id'    => $this->user->id,
            'app_id'     => $this->ssoApp->id,
            'expires_at' => Carbon::now()->subHours(2),
            'used_at'    => null,
        ]);

        SsoToken::create([
            'jti'        => 'thirty-min-old-token',
            'user_id'    => $this->user->id,
            'app_id'     => $this->ssoApp->id,
            'expires_at' => Carbon::now()->subMinutes(30),
            'used_at'    => null,
        ]);

        $this->artisan('sso:prune-tokens', ['--hours' => 1])->assertSuccessful();

        expect(SsoToken::query()->where('jti', 'two-hour-old-token')->exists())->toBeFalse();
        expect(SsoToken::query()->where('jti', 'thirty-min-old-token')->exists())->toBeTrue();
    });

    it('outputs the number of deleted tokens', function () {
        SsoToken::create([
            'jti'        => 'token-a',
            'user_id'    => $this->user->id,
            'app_id'     => $this->ssoApp->id,
            'expires_at' => Carbon::now()->subHours(25),
            'used_at'    => null,
        ]);

        SsoToken::create([
            'jti'        => 'token-b',
            'user_id'    => $this->user->id,
            'app_id'     => $this->ssoApp->id,
            'expires_at' => Carbon::now()->subHours(25),
            'used_at'    => null,
        ]);

        $this->artisan('sso:prune-tokens')
            ->expectsOutputToContain('Deleted 2 expired SSO token(s).')
            ->assertSuccessful();
    });

    it('outputs zero when no tokens are expired', function () {
        SsoToken::create([
            'jti'        => 'valid-token',
            'user_id'    => $this->user->id,
            'app_id'     => $this->ssoApp->id,
            'expires_at' => Carbon::now()->addHour(),
            'used_at'    => null,
        ]);

        $this->artisan('sso:prune-tokens')
            ->expectsOutputToContain('Deleted 0 expired SSO token(s).')
            ->assertSuccessful();
    });
});
