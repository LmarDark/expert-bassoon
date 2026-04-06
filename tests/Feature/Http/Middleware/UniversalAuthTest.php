<?php

declare(strict_types=1);

use App\Models\User;

describe('UniversalAuth Middleware', function () {
    describe('when user is authenticated', function () {
        it('redirects to dashboard when accessing login page', function () {
            $user = User::factory()->create();

            $response = $this->actingAs($user)->get(route('login'));

            $response->assertRedirect(route('dashboard'));
        });

        it('allows access to dashboard', function () {
            $user = User::factory()->create();

            $response = $this->actingAs($user)->get(route('dashboard'));

            $response->assertOk();
        });
    });

    describe('when user is not authenticated', function () {
        beforeEach(function () {
            // Cria um usuário para que o CheckFirstSetup não intercepte as requisições
            User::factory()->create();
        });

        it('redirects to login when accessing dashboard', function () {
            $response = $this->get(route('dashboard'));

            $response->assertRedirect();
            expect($response->headers->get('Location'))->toContain('login');
        });

        it('allows access to login page', function () {
            $response = $this->get(route('login'));

            $response->assertOk();
        });

        it('includes return_to parameter when redirecting to login', function () {
            $response = $this->get(route('dashboard'));

            expect($response->headers->get('Location'))->toContain('return_to');
        });
    });
});
