<?php

use App\Http\Controllers\HealthController;
use App\Http\Controllers\MetricsController;
use Illuminate\Support\Facades\Route;

// Health check for orchestrators (Docker, Kubernetes, load balancers).
// Does not require authentication.
Route::get('/health', [HealthController::class, 'check']);

// Prometheus metrics endpoint for monitoring and observability.
// Does not require authentication (Prometheus scraper needs access).
Route::get('/metrics', [MetricsController::class, 'index']);

Route::get('/', function () {
    return view('welcome');
});
