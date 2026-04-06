<?php

declare(strict_types=1);

use App\Models\User;

test('returns a successful response', function () {
    User::factory()->create();

    $response = $this->get(route('login'));

    $response->assertOk();
});
