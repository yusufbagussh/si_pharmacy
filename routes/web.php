<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuthController;


Route::get('/', function () {
    return redirect('/pharmacies/dashboard/orders');
});

Route::group(['prefix' => 'pharmacies/dashboard'], function () {
    Route::controller(DashboardController::class)->group(function () {
        Route::get('locations', 'location')->name('pharmacies.dashboard.locations');
        Route::get('payers', 'payer')->name('pharmacies.dashboard.payers');
        Route::get('orders', 'order')->name('pharmacies.dashboard.orders');
    });
});

// Route::get('/pharmacies/dashboard/locations', [DashboardController::class, 'location'])->name('pharmacies.dashboard.locations');
// Route::get('/pharmacies/dashboard/payers', [DashboardController::class, 'payer'])->name('pharmacies.dashboard.payers');
// Route::get('/pharmacies/dashboard/orders', [DashboardController::class, 'order'])->name('pharmacies.dashboard.orders')->middleware('auth');

/**
 * Route Autentikasi
 */
Route::get('/login', [AuthController::class, 'index'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'authenticate'])->name('authenticate');
Route::post('/logout', [AuthController::class, 'logout']);

Route::get('/change-password', [AuthController::class, 'changePassword'])->name('change-password');
Route::post('/change-password', [AuthController::class, 'updatePassword'])->name('update-password')->middleware('auth');

Route::resource('/receipts', \App\Http\Controllers\ReceiptController::class);

Route::get('/test-connection', function () {
    try {
        DB::connection()->getPdo();
        echo "Connected successfully to the database!";
    } catch (\Exception $e) {
        die("Could not connect to the database. Error: " . $e->getMessage());
    }
});
