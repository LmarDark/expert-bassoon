<?php

declare(strict_types=1);

use App\Models\ActivityLog;
use App\Models\Setting;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
});

describe('UserController', function () {
    describe('index', function () {
        it('renders users list for admin', function () {
            User::factory()->count(3)->create();

            $response = $this->actingAs($this->admin)->get(route('admin.users.index'));

            $response->assertOk();
            $response->assertInertia(fn ($page) => $page
                ->component('Admin/Users/Index')
                ->has('users', 4)
            );
        });

        it('returns users ordered by created_at descending', function () {
            $oldest = User::factory()->create(['created_at' => now()->subDays(2)]);
            $newest = User::factory()->create(['created_at' => now()->addMinutes(5)]);

            $response = $this->actingAs($this->admin)->get(route('admin.users.index'));

            $users = $response->original->getData()['page']['props']['users'];
            expect($users[0]['id'])->toBe($newest->id);
            expect($users[2]['id'])->toBe($oldest->id);
        });

        it('includes is_admin in users list', function () {
            $response = $this->actingAs($this->admin)->get(route('admin.users.index'));

            $users = $response->original->getData()['page']['props']['users'];
            expect($users[0])->toHaveKey('is_admin');
        });

        it('denies access to non-admin', function () {
            $user = User::factory()->create();

            $this->actingAs($user)->get(route('admin.users.index'))->assertForbidden();
        });
    });

    describe('create', function () {
        it('renders create form for admin', function () {
            $response = $this->actingAs($this->admin)->get(route('admin.users.create'));

            $response->assertOk();
            $response->assertInertia(fn ($page) => $page
                ->component('Admin/Users/Create')
                ->has('usernameValidationType')
            );
        });

        it('passes the configured username validation type to the view', function () {
            Setting::set('username_validation_type', 'cpf');

            $response = $this->actingAs($this->admin)->get(route('admin.users.create'));

            $response->assertInertia(fn ($page) => $page
                ->where('usernameValidationType', 'cpf')
            );
        });
    });

    describe('store', function () {
        it('creates a new user', function () {
            $response = $this->actingAs($this->admin)->post(route('admin.users.store'), [
                'username'              => 'newuser',
                'password'              => 'password123',
                'password_confirmation' => 'password123',
            ]);

            $response->assertRedirect(route('admin.users.index'));
            expect(User::query()->where('username', 'newuser')->exists())->toBeTrue();
        });

        it('creates user with nickname', function () {
            $this->actingAs($this->admin)->post(route('admin.users.store'), [
                'username'              => 'newuser',
                'nickname'              => 'João Silva',
                'password'              => 'password123',
                'password_confirmation' => 'password123',
            ]);

            expect(User::query()->where('username', 'newuser')->first()->nickname)->toBe('João Silva');
        });

        it('creates admin user when is_admin is true', function () {
            $this->actingAs($this->admin)->post(route('admin.users.store'), [
                'username'              => 'newadmin',
                'password'              => 'password123',
                'password_confirmation' => 'password123',
                'is_admin'              => true,
            ]);

            expect(User::query()->where('username', 'newadmin')->first()->is_admin)->toBeTrue();
        });

        it('creates non-admin user when is_admin is omitted', function () {
            $this->actingAs($this->admin)->post(route('admin.users.store'), [
                'username'              => 'normaluser',
                'password'              => 'password123',
                'password_confirmation' => 'password123',
            ]);

            expect(User::query()->where('username', 'normaluser')->first()->is_admin)->toBeFalse();
        });

        it('hashes the password on creation', function () {
            $this->actingAs($this->admin)->post(route('admin.users.store'), [
                'username'              => 'newuser',
                'password'              => 'password123',
                'password_confirmation' => 'password123',
            ]);

            $user = User::query()->where('username', 'newuser')->first();
            expect($user->password)->not->toBe('password123');
        });

        it('logs user_created activity', function () {
            $this->actingAs($this->admin)->post(route('admin.users.store'), [
                'username'              => 'newuser',
                'password'              => 'password123',
                'password_confirmation' => 'password123',
            ]);

            expect(ActivityLog::query()
                ->where('event', 'user_created')
                ->where('target_username', 'newuser')
                ->where('actor_id', $this->admin->id)
                ->exists()
            )->toBeTrue();
        });

        it('validates username with cpf rule from settings', function () {
            Setting::set('username_validation_type', 'cpf');

            $response = $this->actingAs($this->admin)->post(route('admin.users.store'), [
                'username'              => '111.111.111-11',
                'password'              => 'password123',
                'password_confirmation' => 'password123',
            ]);

            $response->assertSessionHasErrors('username');
        });

        it('fails with duplicate username', function () {
            User::factory()->create(['username' => 'existing']);

            $response = $this->actingAs($this->admin)->post(route('admin.users.store'), [
                'username'              => 'existing',
                'password'              => 'password123',
                'password_confirmation' => 'password123',
            ]);

            $response->assertSessionHasErrors('username');
        });

        it('fails when passwords do not match', function () {
            $response = $this->actingAs($this->admin)->post(route('admin.users.store'), [
                'username'              => 'newuser',
                'password'              => 'password123',
                'password_confirmation' => 'different123',
            ]);

            $response->assertSessionHasErrors('password');
        });

        it('denies access to non-admin', function () {
            $user = User::factory()->create();

            $this->actingAs($user)->post(route('admin.users.store'), [
                'username'              => 'newuser',
                'password'              => 'password123',
                'password_confirmation' => 'password123',
            ])->assertForbidden();
        });
    });

    describe('edit', function () {
        it('renders edit form for admin', function () {
            $target = User::factory()->create();

            $response = $this->actingAs($this->admin)->get(route('admin.users.edit', $target));

            $response->assertOk();
            $response->assertInertia(fn ($page) => $page
                ->component('Admin/Users/Edit')
                ->where('user.id', $target->id)
                ->where('user.username', $target->username)
                ->has('user.is_admin')
                ->has('user.nickname')
                ->has('usernameValidationType')
            );
        });
    });

    describe('update', function () {
        it('updates username', function () {
            $target = User::factory()->create(['username' => 'oldname']);

            $this->actingAs($this->admin)->put(route('admin.users.update', $target), [
                'username' => 'newname',
                'is_admin' => $target->is_admin,
            ]);

            expect($target->fresh()->username)->toBe('newname');
        });

        it('updates nickname', function () {
            $target = User::factory()->create();

            $this->actingAs($this->admin)->put(route('admin.users.update', $target), [
                'username' => $target->username,
                'nickname' => 'Novo Apelido',
                'is_admin' => $target->is_admin,
            ]);

            expect($target->fresh()->nickname)->toBe('Novo Apelido');
        });

        it('promotes user to admin', function () {
            $target = User::factory()->create(['is_admin' => false]);

            $this->actingAs($this->admin)->put(route('admin.users.update', $target), [
                'username' => $target->username,
                'is_admin' => true,
            ]);

            expect($target->fresh()->is_admin)->toBeTrue();
        });

        it('demotes another user from admin', function () {
            $target = User::factory()->admin()->create();

            $this->actingAs($this->admin)->put(route('admin.users.update', $target), [
                'username' => $target->username,
                'is_admin' => false,
            ]);

            expect($target->fresh()->is_admin)->toBeFalse();
        });

        it('prevents self-demotion', function () {
            $response = $this->actingAs($this->admin)->put(route('admin.users.update', $this->admin), [
                'username' => $this->admin->username,
                'is_admin' => false,
            ]);

            $response->assertSessionHasErrors('is_admin');
            expect($this->admin->fresh()->is_admin)->toBeTrue();
        });

        it('updates password when provided', function () {
            $target      = User::factory()->create();
            $oldPassword = $target->password;

            $this->actingAs($this->admin)->put(route('admin.users.update', $target), [
                'username'              => $target->username,
                'password'              => 'newpassword123',
                'password_confirmation' => 'newpassword123',
                'is_admin'              => $target->is_admin,
            ]);

            expect($target->fresh()->password)->not->toBe($oldPassword);
        });

        it('does not change password when left blank', function () {
            $target      = User::factory()->create();
            $oldPassword = $target->password;

            $this->actingAs($this->admin)->put(route('admin.users.update', $target), [
                'username' => $target->username,
                'is_admin' => $target->is_admin,
            ]);

            expect($target->fresh()->password)->toBe($oldPassword);
        });

        it('fails with duplicate username from another user', function () {
            User::factory()->create(['username' => 'taken']);
            $target = User::factory()->create(['username' => 'myuser']);

            $response = $this->actingAs($this->admin)->put(route('admin.users.update', $target), [
                'username' => 'taken',
                'is_admin' => $target->is_admin,
            ]);

            $response->assertSessionHasErrors('username');
        });

        it('allows keeping the same username', function () {
            $target = User::factory()->create(['username' => 'sameuser']);

            $response = $this->actingAs($this->admin)->put(route('admin.users.update', $target), [
                'username' => 'sameuser',
                'is_admin' => $target->is_admin,
            ]);

            $response->assertRedirect(route('admin.users.index'));
        });

        it('validates username with cpf rule from settings on update', function () {
            Setting::set('username_validation_type', 'cpf');
            $target = User::factory()->create();

            $response = $this->actingAs($this->admin)->put(route('admin.users.update', $target), [
                'username' => '111.111.111-11',
                'is_admin' => $target->is_admin,
            ]);

            $response->assertSessionHasErrors('username');
        });

        it('logs user_updated activity', function () {
            $target = User::factory()->create();

            $this->actingAs($this->admin)->put(route('admin.users.update', $target), [
                'username' => $target->username,
                'is_admin' => $target->is_admin,
            ]);

            expect(ActivityLog::query()
                ->where('event', 'user_updated')
                ->where('actor_id', $this->admin->id)
                ->exists()
            )->toBeTrue();
        });

        it('denies access to non-admin', function () {
            $user   = User::factory()->create();
            $target = User::factory()->create();

            $this->actingAs($user)->put(route('admin.users.update', $target), [
                'username' => 'hacked',
                'is_admin' => false,
            ])->assertForbidden();
        });
    });

    describe('destroy', function () {
        it('deletes user', function () {
            $target = User::factory()->create();

            $this->actingAs($this->admin)->delete(route('admin.users.destroy', $target));

            expect(User::query()->find($target->id))->toBeNull();
        });

        it('redirects to users index after deletion', function () {
            $target = User::factory()->create();

            $response = $this->actingAs($this->admin)->delete(route('admin.users.destroy', $target));

            $response->assertRedirect(route('admin.users.index'));
        });

        it('prevents self-deletion', function () {
            $response = $this->actingAs($this->admin)->delete(route('admin.users.destroy', $this->admin));

            $response->assertForbidden();
            expect(User::query()->find($this->admin->id))->not->toBeNull();
        });

        it('logs user_deleted activity', function () {
            $target = User::factory()->create();

            $this->actingAs($this->admin)->delete(route('admin.users.destroy', $target));

            expect(ActivityLog::query()
                ->where('event', 'user_deleted')
                ->where('actor_id', $this->admin->id)
                ->exists()
            )->toBeTrue();
        });

        it('denies access to non-admin', function () {
            $user   = User::factory()->create();
            $target = User::factory()->create();

            $this->actingAs($user)->delete(route('admin.users.destroy', $target))->assertForbidden();
            expect(User::query()->find($target->id))->not->toBeNull();
        });
    });
});
