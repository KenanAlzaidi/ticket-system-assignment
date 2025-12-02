<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

// Public Routes
Route::get('/', [TicketController::class, 'create'])->name('tickets.create');
Route::post('/tickets', [TicketController::class, 'store'])->name('tickets.store');

// Admin Guest Routes
Route::middleware('guest')->group(function () {
    Route::get('/admin/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/admin/login', [AuthController::class, 'login'])->name('login.post');
});

// Admin Auth Routes
Route::middleware('auth')->group(function () {
    Route::post('/admin/logout', [AuthController::class, 'logout'])->name('logout');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');
        Route::get('/tickets/{department}/{id}', [TicketController::class, 'show'])->name('tickets.show');
        Route::put('/tickets/{department}/{id}', [TicketController::class, 'update'])->name('tickets.update');
    });
});

// Fallback Route
Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});
