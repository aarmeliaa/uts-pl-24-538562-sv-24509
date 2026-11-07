<?php

use App\Http\Controllers\HealthController;
use App\Http\Controllers\MetricsController;
use Illuminate\Support\Facades\Route;

// Health check for orchestrators (Docker, Kubernetes, load balancers).
// Does not require authentication and skips all middleware to avoid triggering DB queries
// that would fail when database is down.
Route::get('/health', [HealthController::class, 'check'])->withoutMiddleware('web');

// Prometheus metrics endpoint for monitoring and observability.
// Does not require authentication (Prometheus scraper needs access).
// Also skips middleware to avoid unnecessary DB queries.
Route::get('/metrics', [MetricsController::class, 'index'])->withoutMiddleware('web');

Route::get('/', function () {
    return view('welcome');
});
