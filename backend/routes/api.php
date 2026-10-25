<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\MediaController;
use Illuminate\Support\Facades\Route;

// Public: the only endpoint that works without a token.
// throttle:10,1 = max 10 login attempts per minute to slow down password guessing.
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');

// Everything else requires "Authorization: Bearer <token>".
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'me']);

    // index, store, show, update, destroy
    Route::apiResource('media', MediaController::class);
});
