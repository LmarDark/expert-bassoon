<?php

declare(strict_types=1);

use App\Models\User;

// CPF válido para testes: 529.982.247-25
const VALID_CPF = '52998224725';

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
                'username'              => VALID_CPF,
                'validation_type'       => 'cpf',
                'password'              => 'password123',
                'password_confirmation' => 'password123',
            ]);

            $response->assertRedirect(route('home'));
            $this->assertAuthenticated();
            expect(User::query()->count())->toBe(1);
            expect(User::query()->first()->is_admin)->toBeTrue();
        });

        it('stores the correct username', function () {
            $this->post(route('setup.store'), [
                'username'              => VALID_CPF,
                'validation_type'       => 'cpf',
                'password'              => 'password123',
                'password_confirmation' => 'password123',
            ]);

            expect(User::query()->first()->username)->toBe(VALID_CPF);
        });

        it('hashes the password', function () {
            $this->post(route('setup.store'), [
                'username'              => VALID_CPF,
                'validation_type'       => 'cpf',
                'password'              => 'password123',
                'password_confirmation' => 'password123',
            ]);

            expect(User::query()->first()->password)->not->toBe('password123');
        });

        it('fails when username is missing', function () {
            $response = $this->post(route('setup.store'), [
                'validation_type'       => 'cpf',
                'password'              => 'password123',
                'password_confirmation' => 'password123',
            ]);

            $response->assertSessionHasErrors('username');
            expect(User::query()->count())->toBe(0);
        });

        it('fails when password is too short', function () {
            $response = $this->post(route('setup.store'), [
                'username'              => VALID_CPF,
                'validation_type'       => 'cpf',
                'password'              => 'short',
                'password_confirmation' => 'short',
            ]);

            $response->assertSessionHasErrors('password');
            expect(User::query()->count())->toBe(0);
        });

        it('fails when passwords do not match', function () {
            $response = $this->post(route('setup.store'), [
                'username'              => VALID_CPF,
                'validation_type'       => 'cpf',
                'password'              => 'password123',
                'password_confirmation' => 'different123',
            ]);

            $response->assertSessionHasErrors('password');
            expect(User::query()->count())->toBe(0);
        });

        it('returns 403 when users already exist', function () {
            User::factory()->create();

            $response = $this->post(route('setup.store'), [
                'username'              => VALID_CPF,
                'validation_type'       => 'cpf',
                'password'              => 'password123',
                'password_confirmation' => 'password123',
            ]);

            $response->assertRedirect(route('login'));
            expect(User::query()->count())->toBe(1);
        });
    });

    describe('username validation', function () {
        describe('CPF', function () {
            it('accepts a valid CPF', function () {
                $response = $this->post(route('setup.store'), [
                    'username'              => VALID_CPF,
                    'validation_type'       => 'cpf',
                    'password'              => 'password123',
                    'password_confirmation' => 'password123',
                ]);

                $response->assertRedirect(route('home'));
            });

            it('accepts a formatted CPF', function () {
                $response = $this->post(route('setup.store'), [
                    'username'              => '529.982.247-25',
                    'validation_type'       => 'cpf',
                    'password'              => 'password123',
                    'password_confirmation' => 'password123',
                ]);

                $response->assertRedirect(route('home'));
            });

            it('rejects an invalid CPF', function () {
                $response = $this->post(route('setup.store'), [
                    'username'              => '12345678900',
                    'validation_type'       => 'cpf',
                    'password'              => 'password123',
                    'password_confirmation' => 'password123',
                ]);

                $response->assertSessionHasErrors('username');
            });

            it('rejects a CPF with all repeated digits', function () {
                $response = $this->post(route('setup.store'), [
                    'username'              => '11111111111',
                    'validation_type'       => 'cpf',
                    'password'              => 'password123',
                    'password_confirmation' => 'password123',
                ]);

                $response->assertSessionHasErrors('username');
            });
        });

        describe('Celular', function () {
            it('accepts a valid celular without formatting', function () {
                $response = $this->post(route('setup.store'), [
                    'username'              => '11912345678',
                    'validation_type'       => 'celular',
                    'password'              => 'password123',
                    'password_confirmation' => 'password123',
                ]);

                $response->assertRedirect(route('home'));
            });

            it('accepts a formatted celular', function () {
                $response = $this->post(route('setup.store'), [
                    'username'              => '(11) 91234-5678',
                    'validation_type'       => 'celular',
                    'password'              => 'password123',
                    'password_confirmation' => 'password123',
                ]);

                $response->assertRedirect(route('home'));
            });

            it('rejects a celular without the digit 9', function () {
                $response = $this->post(route('setup.store'), [
                    'username'              => '1112345678',
                    'validation_type'       => 'celular',
                    'password'              => 'password123',
                    'password_confirmation' => 'password123',
                ]);

                $response->assertSessionHasErrors('username');
            });

            it('rejects an invalid celular', function () {
                $response = $this->post(route('setup.store'), [
                    'username'              => '12345678',
                    'validation_type'       => 'celular',
                    'password'              => 'password123',
                    'password_confirmation' => 'password123',
                ]);

                $response->assertSessionHasErrors('username');
            });
        });

        describe('Personalizado', function () {
            it('accepts username matching the custom pattern', function () {
                $response = $this->post(route('setup.store'), [
                    'username'              => 'ABC123',
                    'validation_type'       => 'personalizado',
                    'custom_pattern'        => '^[A-Z0-9]+$',
                    'password'              => 'password123',
                    'password_confirmation' => 'password123',
                ]);

                $response->assertRedirect(route('home'));
            });

            it('rejects username not matching the custom pattern', function () {
                $response = $this->post(route('setup.store'), [
                    'username'              => 'abc123',
                    'validation_type'       => 'personalizado',
                    'custom_pattern'        => '^[A-Z0-9]+$',
                    'password'              => 'password123',
                    'password_confirmation' => 'password123',
                ]);

                $response->assertSessionHasErrors('username');
            });

            it('requires custom_pattern when validation type is personalizado', function () {
                $response = $this->post(route('setup.store'), [
                    'username'              => 'myusername',
                    'validation_type'       => 'personalizado',
                    'password'              => 'password123',
                    'password_confirmation' => 'password123',
                ]);

                $response->assertSessionHasErrors('custom_pattern');
            });

            it('rejects an invalid regex as custom_pattern', function () {
                $response = $this->post(route('setup.store'), [
                    'username'              => 'myusername',
                    'validation_type'       => 'personalizado',
                    'custom_pattern'        => '[invalid(regex',
                    'password'              => 'password123',
                    'password_confirmation' => 'password123',
                ]);

                $response->assertSessionHasErrors('custom_pattern');
            });
        });
    });
});
