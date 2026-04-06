<?php

declare(strict_types=1);

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

        it('denies access to non-admin', function () {
            $user = User::factory()->create();

            $this->actingAs($user)->get(route('admin.users.index'))->assertForbidden();
        });
    });

    describe('create', function () {
        it('renders create form for admin', function () {
            $response = $this->actingAs($this->admin)->get(route('admin.users.create'));

            $response->assertOk();
            $response->assertInertia(fn ($page) => $page->component('Admin/Users/Create'));
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

        it('hashes the password on creation', function () {
            $this->actingAs($this->admin)->post(route('admin.users.store'), [
                'username'              => 'newuser',
                'password'              => 'password123',
                'password_confirmation' => 'password123',
            ]);

            $user = User::query()->where('username', 'newuser')->first();
            expect($user->password)->not->toBe('password123');
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
            );
        });
    });

    describe('update', function () {
        it('updates username', function () {
            $target = User::factory()->create(['username' => 'oldname']);

            $this->actingAs($this->admin)->put(route('admin.users.update', $target), [
                'username' => 'newname',
            ]);

            expect($target->fresh()->username)->toBe('newname');
        });

        it('updates password when provided', function () {
            $target      = User::factory()->create();
            $oldPassword = $target->password;

            $this->actingAs($this->admin)->put(route('admin.users.update', $target), [
                'username'              => $target->username,
                'password'              => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ]);

            expect($target->fresh()->password)->not->toBe($oldPassword);
        });

        it('does not change password when left blank', function () {
            $target      = User::factory()->create();
            $oldPassword = $target->password;

            $this->actingAs($this->admin)->put(route('admin.users.update', $target), [
                'username' => $target->username,
            ]);

            expect($target->fresh()->password)->toBe($oldPassword);
        });

        it('fails with duplicate username from another user', function () {
            User::factory()->create(['username' => 'taken']);
            $target = User::factory()->create(['username' => 'myuser']);

            $response = $this->actingAs($this->admin)->put(route('admin.users.update', $target), [
                'username' => 'taken',
            ]);

            $response->assertSessionHasErrors('username');
        });

        it('allows keeping the same username', function () {
            $target = User::factory()->create(['username' => 'sameuser']);

            $response = $this->actingAs($this->admin)->put(route('admin.users.update', $target), [
                'username' => 'sameuser',
            ]);

            $response->assertRedirect(route('admin.users.index'));
        });

        it('denies access to non-admin', function () {
            $user   = User::factory()->create();
            $target = User::factory()->create();

            $this->actingAs($user)->put(route('admin.users.update', $target), [
                'username' => 'hacked',
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

        it('denies access to non-admin', function () {
            $user   = User::factory()->create();
            $target = User::factory()->create();

            $this->actingAs($user)->delete(route('admin.users.destroy', $target))->assertForbidden();
            expect(User::query()->find($target->id))->not->toBeNull();
        });
    });
});
