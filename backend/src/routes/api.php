<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\CityController;
use App\Http\Controllers\Api\CourtController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group.
|
*/

// Public routes (with rate limiting for auth)
Route::middleware('throttle:auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// Public routes (with general API rate limiting)
Route::middleware('throttle:api')->group(function () {
    // Cities
    Route::get('/cities', [CityController::class, 'index']);
    Route::get('/cities/{identifier}', [CityController::class, 'show']);

    // Courts
    Route::get('/courts', [CourtController::class, 'index']);
    Route::get('/courts/popular', [CourtController::class, 'popular']);
    Route::get('/courts/featured', [CourtController::class, 'featured']);
    Route::get('/courts/{id}', [CourtController::class, 'show']);
    Route::get('/courts/{id}/available-slots', [CourtController::class, 'availableSlots']);
});

// Protected routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/user/update', [AuthController::class, 'updateProfile']);
    Route::post('/user/change-password', [AuthController::class, 'changePassword']);

    // Bookings
    Route::get('/bookings', [BookingController::class, 'index']);
    Route::get('/bookings/next', [BookingController::class, 'nextBooking']);
    Route::post('/bookings', [BookingController::class, 'store']);
    Route::post('/bookings/{id}/confirm', [BookingController::class, 'confirm']);
    Route::delete('/bookings/{id}', [BookingController::class, 'cancel']);
});
