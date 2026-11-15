<?php

use App\Http\Controllers\Api\V1\AccountController;
use App\Http\Controllers\Api\V1\AuthTokenController;
use App\Http\Controllers\Api\V1\StudentApplicationController;
use App\Http\Controllers\PaymentWebhookController;
use Illuminate\Support\Facades\Route;

/*
 * Versioned JSON API for future mobile apps. See docs/API.md.
 * Authentication: "Authorization: Bearer <token>" from POST /api/v1/auth/tokens.
 */
Route::post('/webhooks/payments/{provider}', PaymentWebhookController::class)
    ->where('provider', '[a-z0-9-]+')->middleware('throttle:60,1')->name('api.webhooks.payments');

Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::post('/auth/tokens', [AuthTokenController::class, 'store'])->middleware('throttle:api-auth')->name('auth.tokens.store');

    Route::middleware('auth:api')->group(function () {
        Route::delete('/auth/tokens/current', [AuthTokenController::class, 'destroy'])->name('auth.tokens.destroy');
        Route::get('/me', [AccountController::class, 'me'])->name('me');
        Route::get('/notifications', [AccountController::class, 'notifications'])->name('notifications.index');
        Route::post('/notifications/read', [AccountController::class, 'markNotificationsRead'])->name('notifications.read');
        Route::get('/applications', [StudentApplicationController::class, 'index'])->name('applications.index');
        Route::get('/applications/{application}', [StudentApplicationController::class, 'show'])->whereNumber('application')->name('applications.show');
    });
});
