<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;


Route::get('/', function () {
    return redirect('/pharmacies/dashboard/orders');
});
Route::get('/pharmacies/dashboard/locations', [DashboardController::class, 'location'])->name('pharmacies.dashboard.locations');
Route::get('/pharmacies/dashboard/payers', [DashboardController::class, 'payer'])->name('pharmacies.dashboard.payers');
Route::get('/pharmacies/dashboard/orders', [DashboardController::class, 'order'])->name('pharmacies.dashboard.orders');



Route::get('/test-connection', function () {
    try {
        DB::connection()->getPdo();
        echo "Connected successfully to the database!";
    } catch (\Exception $e) {
        die("Could not connect to the database. Error: " . $e->getMessage());
    }
});
