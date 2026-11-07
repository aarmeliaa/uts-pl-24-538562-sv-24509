<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

/**
 * Health check endpoint for orchestrators (Docker, Kubernetes, load balancers).
 * Returns 200 if the service is healthy, 503 if unhealthy.
 */
class HealthController extends Controller
{
    /**
     * GET /health
     * 
     * Checks critical dependencies (database connection).
     * This endpoint does not require authentication.
     */
    public function check(): JsonResponse
    {
        try {
            // Check database connection with a simple query.
            DB::connection()->getPdo();
            DB::select('SELECT 1');

            return response()->json([
                'status' => 'healthy',
                'timestamp' => now()->toIso8601String(),
                'service' => 'media-tracker-api',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'unhealthy',
                'error' => 'Database connection failed',
                'message' => $e->getMessage(),
                'timestamp' => now()->toIso8601String(),
            ], 503);
        }
    }
}
