<?php

declare(strict_types=1);

use App\Models\App;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->user  = User::factory()->create();
});

describe('AppController', function () {
    describe('index', function () {
        it('renders apps list for admin', function () {
            $this->actingAs($this->admin)
                ->get(route('admin.apps.index'))
                ->assertOk()
                ->assertInertia(fn ($page) => $page->component('Admin/Apps/Index'));
        });

        it('denies access to non-admin', function () {
            $this->actingAs($this->user)
                ->get(route('admin.apps.index'))
                ->assertForbidden();
        });

        it('returns apps ordered by created_at desc', function () {
            $this->travelTo(now()->subMinutes(5));
            $old = App::factory()->create(['name' => 'Antiga']);
            $this->travelBack();
            $new = App::factory()->create(['name' => 'Nova']);

            $this->actingAs($this->admin)
                ->get(route('admin.apps.index'))
                ->assertInertia(fn ($page) => $page
                    ->where('apps.0.id', $new->id)
                    ->where('apps.1.id', $old->id)
                );
        });
    });

    describe('create', function () {
        it('renders create form for admin', function () {
            $this->actingAs($this->admin)
                ->get(route('admin.apps.create'))
                ->assertOk()
                ->assertInertia(fn ($page) => $page->component('Admin/Apps/Create'));
        });
    });

    describe('store', function () {
        it('creates a new app with generated api_key', function () {
            $this->actingAs($this->admin)
                ->post(route('admin.apps.store'), [
                    'name'            => 'Meu App',
                    'allowed_domains' => "app.exemplo.com\napi.exemplo.com",
                    'callback_url'    => '',
                    'active'          => true,
                ]);

            $app = App::query()->where('name', 'Meu App')->first();
            expect($app)->not->toBeNull();
            expect($app->api_key)->toHaveLength(64);
            expect($app->allowed_domains)->toBe(['app.exemplo.com', 'api.exemplo.com']);
            expect($app->active)->toBeTrue();
        });

        it('redirects to index with success flash', function () {
            $response = $this->actingAs($this->admin)
                ->post(route('admin.apps.store'), [
                    'name'            => 'App Teste',
                    'allowed_domains' => 'teste.com',
                ]);

            $response->assertRedirect(route('admin.apps.index'))
                ->assertSessionHas('success');
        });

        it('fails when name is missing', function () {
            $this->actingAs($this->admin)
                ->post(route('admin.apps.store'), ['allowed_domains' => 'teste.com'])
                ->assertSessionHasErrors('name');
        });

        it('fails when allowed_domains is missing', function () {
            $this->actingAs($this->admin)
                ->post(route('admin.apps.store'), ['name' => 'App'])
                ->assertSessionHasErrors('allowed_domains');
        });

        it('fails with invalid callback_url', function () {
            $this->actingAs($this->admin)
                ->post(route('admin.apps.store'), [
                    'name'            => 'App',
                    'allowed_domains' => 'teste.com',
                    'callback_url'    => 'not-a-url',
                ])
                ->assertSessionHasErrors('callback_url');
        });

        it('denies access to non-admin', function () {
            $this->actingAs($this->user)
                ->post(route('admin.apps.store'), ['name' => 'App', 'allowed_domains' => 'x.com'])
                ->assertForbidden();
        });
    });

    describe('edit', function () {
        it('renders edit form for admin', function () {
            $app = App::factory()->create();

            $this->actingAs($this->admin)
                ->get(route('admin.apps.edit', $app))
                ->assertOk()
                ->assertInertia(fn ($page) => $page->component('Admin/Apps/Edit'));
        });
    });

    describe('update', function () {
        it('updates app fields', function () {
            $app = App::factory()->create(['name' => 'Antigo', 'allowed_domains' => ['old.com']]);

            $this->actingAs($this->admin)
                ->put(route('admin.apps.update', $app), [
                    'name'            => 'Novo',
                    'allowed_domains' => 'new.com',
                    'active'          => true,
                ]);

            expect($app->fresh()->name)->toBe('Novo');
            expect($app->fresh()->allowed_domains)->toBe(['new.com']);
        });

        it('redirects with success flash after update', function () {
            $app = App::factory()->create();

            $this->actingAs($this->admin)
                ->put(route('admin.apps.update', $app), [
                    'name'            => 'App',
                    'allowed_domains' => 'a.com',
                    'active'          => true,
                ])
                ->assertRedirect(route('admin.apps.index'))
                ->assertSessionHas('success');
        });

        it('denies access to non-admin', function () {
            $app = App::factory()->create();

            $this->actingAs($this->user)
                ->put(route('admin.apps.update', $app), ['name' => 'x', 'allowed_domains' => 'x.com', 'active' => true])
                ->assertForbidden();
        });
    });

    describe('destroy', function () {
        it('deletes the app', function () {
            $app = App::factory()->create();

            $this->actingAs($this->admin)
                ->delete(route('admin.apps.destroy', $app));

            expect(App::query()->find($app->id))->toBeNull();
        });

        it('redirects with success flash after delete', function () {
            $app = App::factory()->create();

            $this->actingAs($this->admin)
                ->delete(route('admin.apps.destroy', $app))
                ->assertRedirect(route('admin.apps.index'))
                ->assertSessionHas('success');
        });

        it('denies access to non-admin', function () {
            $app = App::factory()->create();

            $this->actingAs($this->user)
                ->delete(route('admin.apps.destroy', $app))
                ->assertForbidden();
        });
    });

    describe('regenerateApiKey', function () {
        it('generates a new api_key', function () {
            $app     = App::factory()->create();
            $oldKey  = $app->api_key;

            $this->actingAs($this->admin)
                ->post(route('admin.apps.regenerate-key', $app));

            expect($app->fresh()->api_key)->not->toBe($oldKey);
            expect($app->fresh()->api_key)->toHaveLength(64);
        });

        it('redirects to edit with success flash', function () {
            $app = App::factory()->create();

            $this->actingAs($this->admin)
                ->post(route('admin.apps.regenerate-key', $app))
                ->assertRedirect(route('admin.apps.edit', $app))
                ->assertSessionHas('success');
        });
    });
});
