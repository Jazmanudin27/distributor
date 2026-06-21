<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'sales' => \App\Http\Middleware\SalesMiddleware::class,
            'owner.mobile' => \App\Http\Middleware\OwnerMobileMiddleware::class,
        ]);

        // Auto-sync role lama (kolom users.role) ke Spatie Permission
        $middleware->appendToGroup('web', \App\Http\Middleware\SyncLegacyRole::class);
        
        $middleware->redirectGuestsTo(function ($request) {
            $uri = $request->getRequestUri();
            if (str_starts_with($uri, '/m/owner')) {
                return route('mobile.owner.login');
            } elseif (str_starts_with($uri, '/m')) {
                return route('mobile.login');
            }
            return route('login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
