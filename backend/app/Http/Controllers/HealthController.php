<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

/**
 * Health check endpoint for orchestrators (Docker, Kubernetes, load balancers).
 * Returns 200 if the service is healthy, 503 if unhealthy.
 * 
 * This controller bypasses all middleware to ensure it can detect failures
 * independently. No session or query middleware interference.
 */
class HealthController extends Controller
{
    /**
     * GET /health
     * 
     * Checks critical dependencies (database connection).
     * This endpoint does not require authentication.
     * 
     * Always returns JSON, even on error, to ensure orchestrators can parse the response.
     */
    public function check(): Response
    {
        try {
            // Check database connection with a simple query.
            // Use raw connection to avoid triggering middleware or query events.
            DB::connection()->getPdo();
            DB::select('SELECT 1');

            return response()
                ->json([
                    'status' => 'healthy',
                    'timestamp' => now()->toIso8601String(),
                    'service' => 'media-tracker-api',
                ], 200)
                ->header('Content-Type', 'application/json');

        } catch (\PDOException $e) {
            // Database connection failed
            return response()
                ->json([
                    'status' => 'unhealthy',
                    'error' => 'Database connection failed',
                    'timestamp' => now()->toIso8601String(),
                ], 503)
                ->header('Content-Type', 'application/json');

        } catch (\Throwable $e) {
            // Any other error
            return response()
                ->json([
                    'status' => 'unhealthy',
                    'error' => 'Service check failed',
                    'timestamp' => now()->toIso8601String(),
                ], 503)
                ->header('Content-Type', 'application/json');
        }
    }
}
