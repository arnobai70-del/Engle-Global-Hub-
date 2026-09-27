<?php

use App\Http\Middleware\EnsureFeatureIsVisible;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function (): void {
            /*
             * Hotels, Tours and Visa landing pages are catalogue/presentation
             * pages and must remain browseable before sign-in. The routes live
             * inside the legacy authenticated group in routes/web.php, so only
             * those three GET routes explicitly exclude authentication,
             * verification and search/view permissions here. Their feature
             * visibility middleware remains active, and all POST searches and
             * other protected customer actions keep the original middleware.
             */
            $publicTravelPages = [
                'hotels.index' => ['auth', 'verified', 'permission:hotels.search'],
                'tours.index' => ['auth', 'verified', 'permission:tours.search'],
                'visa.index' => ['auth', 'verified', 'permission:visa.view'],
            ];

            foreach ($publicTravelPages as $routeName => $middleware) {
                Route::getRoutes()
                    ->getByName($routeName)
                    ?->withoutMiddleware($middleware);
            }
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'feature' => EnsureFeatureIsVisible::class,
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();
