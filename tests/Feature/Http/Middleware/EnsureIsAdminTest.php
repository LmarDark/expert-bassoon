<?php

declare(strict_types=1);

use App\Models\User;

describe('EnsureIsAdmin Middleware', function () {
    it('allows admin user to access admin routes', function () {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('admin.users.index'));

        $response->assertOk();
    });

    it('blocks non-admin user from accessing admin routes', function () {
        $user = User::factory()->create(['is_admin' => false]);

        $response = $this->actingAs($user)->get(route('admin.users.index'));

        $response->assertForbidden();
    });

    it('blocks non-admin user from creating users', function () {
        $user = User::factory()->create(['is_admin' => false]);

        $response = $this->actingAs($user)->post(route('admin.users.store'), [
            'username' => 'newuser',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertForbidden();
    });

    it('blocks non-admin user from editing users', function () {
        $user  = User::factory()->create(['is_admin' => false]);
        $other = User::factory()->create();

        $response = $this->actingAs($user)->get(route('admin.users.edit', $other));

        $response->assertForbidden();
    });

    it('blocks non-admin user from deleting users', function () {
        $user  = User::factory()->create(['is_admin' => false]);
        $other = User::factory()->create();

        $response = $this->actingAs($user)->delete(route('admin.users.destroy', $other));

        $response->assertForbidden();
    });
});
