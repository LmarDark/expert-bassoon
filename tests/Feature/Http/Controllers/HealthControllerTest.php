<?php

declare(strict_types=1);

describe('HealthController', function () {
    it('returns ok status with 200', function () {
        $response = $this->getJson(route('health'));

        $response->assertOk()
            ->assertJsonStructure(['status', 'database', 'timestamp'])
            ->assertJson(['status' => 'ok', 'database' => 'ok']);
    });

    it('includes an ISO 8601 timestamp', function () {
        $response = $this->getJson(route('health'));

        $timestamp = $response->json('timestamp');
        expect($timestamp)->toBeString();
        expect(\Illuminate\Support\Carbon::parse($timestamp))->not->toBeNull();
    });

    it('is accessible without authentication', function () {
        $this->getJson(route('health'))->assertOk();
    });
});
