<?php

use App\Http\Controllers\Hotel\HotelController;
use App\Http\Controllers\Tour\TourController;
use App\Http\Controllers\Visa\VisaController;
use App\Http\Middleware\ApplyRedirects;
use App\Http\Middleware\EnsureFeatureIsVisible;
use App\Http\Middleware\RedirectWhenPageIsEmpty;
use App\Http\Middleware\SetLocale;
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
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function (): void {
            /*
             * Public travel landing pages.
             *
             * routes/web.php still contains the legacy authenticated GET
             * definitions alongside protected search/action routes. Registering
             * these three GET routes after routes/web.php intentionally replaces
             * only the matching GET URI entries in Laravel's route collection.
             * This keeps catalogue/demo landing pages browseable for guests while
             * POST searches, bookings, payments and other customer actions remain
             * protected by their existing auth/verified/permission middleware.
             */
            Route::middleware('web')->group(function (): void {
                Route::get('/hotels', HotelController::class)
                    ->middleware('feature:hotels')
                    ->name('hotels.index');

                Route::get('/tours', TourController::class)
                    ->middleware('feature:tours')
                    ->name('tours.index');

                Route::get('/visa', VisaController::class)
                    ->middleware('feature:visa')
                    ->name('visa.index');
            });
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Runs before routing so redirected paths that no longer exist still redirect.
        $middleware->append(ApplyRedirects::class);

        $middleware->web(append: [
            SetLocale::class,
        ]);

        $middleware->alias([
            'feature' => EnsureFeatureIsVisible::class,
            'has-content' => RedirectWhenPageIsEmpty::class,
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();
