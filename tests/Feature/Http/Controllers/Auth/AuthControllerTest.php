<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Hash;

const ALLOWED_HOST_REDIRECT_ENV = 'ALLOWED_HOST_REDIRECT=example.com';

beforeEach(function () {
    putenv(ALLOWED_HOST_REDIRECT_ENV);

    $this->user = User::factory()->create([
        'username' => 'testuser',
        'password' => Hash::make('password123'),
    ]);
});

describe('AuthController', function () {
    describe('login', function () {
        it('authenticates user with valid credentials', function () {
            $response = $this->post(route('login.submit'), [
                'username' => 'testuser',
                'password' => 'password123',
            ]);

            $response->assertRedirect();
            $this->assertAuthenticatedAs($this->user);
        });

        it('fails authentication with invalid password', function () {
            $response = $this->post(route('login.submit'), [
                'username' => 'testuser',
                'password' => 'wrongpassword',
            ]);

            $response->assertSessionHasErrors('username');
            $this->assertGuest();
        });

        it('fails authentication with non-existent user', function () {
            $response = $this->post(route('login.submit'), [
                'username' => 'nonexistent',
                'password' => 'password123',
            ]);

            $response->assertSessionHasErrors('username');
            $this->assertGuest();
        });

        it('regenerates session after successful login', function () {
            $oldSessionId = session()->getId();

            $this->post(route('login.submit'), [
                'username' => 'testuser',
                'password' => 'password123',
            ]);

            expect(session()->getId())->not->toBe($oldSessionId);
        });

        it('redirects to dashboard by default after login', function () {
            $response = $this->post(route('login.submit'), [
                'username' => 'testuser',
                'password' => 'password123',
            ]);

            $response->assertRedirect(route('dashboard'));
        });

        it('redirects to return_to URL when valid and safe', function () {
            $response = $this->post(route('login.submit'), [
                'username' => 'testuser',
                'password' => 'password123',
                'return_to' => 'https://app.example.com/page',
            ]);

            $response->assertRedirect('https://app.example.com/page');
        });

        it('redirects to dashboard when return_to host is not allowed', function () {
            $response = $this->post(route('login.submit'), [
                'username' => 'testuser',
                'password' => 'password123',
                'return_to' => 'https://malicious.com/phishing',
            ]);

            $response->assertRedirect(route('dashboard'));
        });

        it('uses session intended URL when return_to is not provided', function () {
            session()->put('url.intended', 'https://app.example.com/intended');

            $response = $this->post(route('login.submit'), [
                'username' => 'testuser',
                'password' => 'password123',
            ]);

            $response->assertRedirect('https://app.example.com/intended');
        });

        it('handles remember me option', function () {
            $this->post(route('login.submit'), [
                'username' => 'testuser',
                'password' => 'password123',
                'remember' => true,
            ]);

            $this->assertAuthenticatedAs($this->user);
        });
    });

    describe('logout', function () {
        it('logs out authenticated user', function () {
            $this->actingAs($this->user);

            $this->post(route('logout'));

            $this->assertGuest();
        });

        it('invalidates session on logout', function () {
            $this->actingAs($this->user);
            $oldSessionId = session()->getId();

            $this->post(route('logout'));

            expect(session()->getId())->not->toBe($oldSessionId);
        });

        it('redirects to login page after logout', function () {
            $this->actingAs($this->user);

            $response = $this->post(route('logout'));

            $response->assertRedirect(route('login'));
        });
    });
});
