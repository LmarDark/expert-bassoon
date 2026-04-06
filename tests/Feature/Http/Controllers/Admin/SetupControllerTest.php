<?php

declare(strict_types=1);

use App\Models\User;

describe('SetupController', function () {
    describe('show', function () {
        it('renders setup page when no users exist', function () {
            $response = $this->get(route('setup'));

            $response->assertOk();
            $response->assertInertia(fn ($page) => $page->component('Admin/Setup'));
        });

        it('redirects to login when users already exist', function () {
            User::factory()->create();

            $response = $this->get(route('setup'));

            $response->assertRedirect(route('login'));
        });
    });

    describe('store', function () {
        it('creates admin user and logs in', function () {
            $response = $this->post(route('setup.store'), [
                'username'              => 'admin',
                'password'              => 'password123',
                'password_confirmation' => 'password123',
            ]);

            $response->assertRedirect(route('dashboard'));
            $this->assertAuthenticated();
            expect(User::query()->count())->toBe(1);
            expect(User::query()->first()->is_admin)->toBeTrue();
        });

        it('stores the correct username', function () {
            $this->post(route('setup.store'), [
                'username'              => 'myadmin',
                'password'              => 'password123',
                'password_confirmation' => 'password123',
            ]);

            expect(User::query()->first()->username)->toBe('myadmin');
        });

        it('hashes the password', function () {
            $this->post(route('setup.store'), [
                'username'              => 'admin',
                'password'              => 'password123',
                'password_confirmation' => 'password123',
            ]);

            expect(User::query()->first()->password)->not->toBe('password123');
        });

        it('fails when username is missing', function () {
            $response = $this->post(route('setup.store'), [
                'password'              => 'password123',
                'password_confirmation' => 'password123',
            ]);

            $response->assertSessionHasErrors('username');
            expect(User::query()->count())->toBe(0);
        });

        it('fails when password is too short', function () {
            $response = $this->post(route('setup.store'), [
                'username'              => 'admin',
                'password'              => 'short',
                'password_confirmation' => 'short',
            ]);

            $response->assertSessionHasErrors('password');
            expect(User::query()->count())->toBe(0);
        });

        it('fails when passwords do not match', function () {
            $response = $this->post(route('setup.store'), [
                'username'              => 'admin',
                'password'              => 'password123',
                'password_confirmation' => 'different123',
            ]);

            $response->assertSessionHasErrors('password');
            expect(User::query()->count())->toBe(0);
        });

        it('returns 403 when users already exist', function () {
            User::factory()->create();

            $response = $this->post(route('setup.store'), [
                'username'              => 'hacker',
                'password'              => 'password123',
                'password_confirmation' => 'password123',
            ]);

            $response->assertRedirect(route('login'));
            expect(User::query()->count())->toBe(1);
        });
    });
});
