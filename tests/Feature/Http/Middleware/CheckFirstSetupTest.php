<?php

declare(strict_types=1);

use App\Models\User;

describe('CheckFirstSetup Middleware', function () {
    describe('when no users exist', function () {
        it('redirects to setup when accessing login', function () {
            $response = $this->get(route('login'));

            $response->assertRedirect(route('setup'));
        });

        it('redirects to setup when accessing dashboard', function () {
            $response = $this->get(route('dashboard'));

            $response->assertRedirect(route('setup'));
        });

        it('allows access to setup page', function () {
            $response = $this->get(route('setup'));

            $response->assertOk();
        });
    });

    describe('when users exist', function () {
        beforeEach(function () {
            User::factory()->create();
        });

        it('redirects to login when accessing setup', function () {
            $response = $this->get(route('setup'));

            $response->assertRedirect(route('login'));
        });

        it('does not redirect login to setup', function () {
            $response = $this->get(route('login'));

            $response->assertOk();
        });
    });
});
