<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // This app has no web login page, so never redirect API guests.
        // Without a redirect target, the request ends as a 401 JSON response.
        $middleware->redirectGuestsTo(function (Request $request) {
            return $request->is('api/*') ? null : '/';
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Always answer API routes with JSON (401, 404, 422, ...),
        // even when the client forgets the "Accept: application/json" header.
        $exceptions->shouldRenderJsonWhen(function (Request $request) {
            return $request->is('api/*') || $request->expectsJson();
        });
    })->create();
