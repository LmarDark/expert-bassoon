<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\App;
use App\Models\SsoToken;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @extends Factory<SsoToken>
 */
final class SsoTokenFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'jti'        => Str::random(32),
            'user_id'    => User::factory(),
            'app_id'     => App::factory(),
            'expires_at' => Carbon::now()->addMinutes(2),
            'used_at'    => null,
        ];
    }

    public function expired(): static
    {
        return $this->state(['expires_at' => Carbon::now()->subMinute()]);
    }

    public function used(): static
    {
        return $this->state(['used_at' => Carbon::now()]);
    }
}
