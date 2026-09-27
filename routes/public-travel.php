<?php

use App\Http\Controllers\Hotel\HotelController;
use App\Http\Controllers\Tour\TourController;
use App\Http\Controllers\Visa\VisaController;
use Illuminate\Support\Facades\Route;

/*
 * Public service landing pages.
 *
 * These endpoints only render catalogue/demo content and do not create
 * bookings, payments, applications, supplier orders or customer records.
 * Search/action POST routes remain protected by auth, verification and
 * permissions in routes/web.php.
 */
Route::get('/hotels', HotelController::class)
    ->middleware('feature:hotels')
    ->name('hotels.index');

Route::get('/tours', TourController::class)
    ->middleware('feature:tours')
    ->name('tours.index');

Route::get('/visa', VisaController::class)
    ->middleware('feature:visa')
    ->name('visa.index');
