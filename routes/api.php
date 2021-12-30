<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PassportAuthController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\PriceController;
use App\Http\Controllers\BookingController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('register', [PassportAuthController::class, 'register']);
Route::post('login', [PassportAuthController::class, 'login']);
Route::resource('rooms', RoomController::class);
Route::resource('prices', PriceController::class);

Route::middleware('auth:api')->group(function () {
    Route::get('bookings-room/{id}', [BookingController::class,'showRoomBookings']);
    Route::get('bookings-user/{id}', [BookingController::class,'showUserBookings']);
    Route::get('mybookings', [BookingController::class,'showMyBookings']);
    Route::resource('bookings', BookingController::class);
    Route::get('rooms/restore/{id}', [RoomController::class, 'restore']);
    Route::get('logout', [PassportAuthController::class, 'logout']);
});
