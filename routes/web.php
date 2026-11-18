<?php

use App\Http\Controllers\BookController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FacilityController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReadingAreaController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');

    /*
    |----------------------------------------------------------------------
    | Modul Perpustakaan - Fasilitas & Area Baca
    |----------------------------------------------------------------------
    */
    Route::resource('reading-areas', ReadingAreaController::class);
    Route::resource('facilities', FacilityController::class);
});

/*
|--------------------------------------------------------------------------
| Operational Modules (Admin & Petugas)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin,petugas'])->group(function () {
    Route::resource('books', BookController::class);
});

/*
|--------------------------------------------------------------------------
| Admin Only Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin'])->group(function () {
    //
});

require __DIR__ . '/auth.php';