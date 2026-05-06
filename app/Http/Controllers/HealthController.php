<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

final class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $dbStatus = 'ok';

        try {
            DB::connection()->getPdo();
        } catch (Throwable) {
            $dbStatus = 'error';
        }

        $status = $dbStatus === 'ok' ? 200 : 503;

        return response()->json([
            'status' => $dbStatus === 'ok' ? 'ok' : 'degraded',
            'database' => $dbStatus,
            'timestamp' => now()->toIso8601String(),
        ], $status);
    }
}
