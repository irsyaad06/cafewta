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
        $middleware->web(prepend: [
            \App\Http\Middleware\TabSessionMiddleware::class,
        ]);
        
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'logout',
            'pos',
            'pos/*',
            'order/*',
        ]);

        $middleware->redirectUsersTo(function (\Illuminate\Http\Request $request) {
            $user = $request->user();
            if ($user && in_array($user->role, [\App\Enums\UserRole::SuperAdmin, \App\Enums\UserRole::Owner, \App\Enums\UserRole::AdminManager])) {
                return '/admin';
            } elseif ($user && $user->role === \App\Enums\UserRole::Cashier) {
                return '/pos';
            } elseif ($user && in_array($user->role, [\App\Enums\UserRole::Kitchen, \App\Enums\UserRole::Waiter])) {
                return '/pos/orders';
            }
            return '/dashboard';
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
