<?php

use App\Http\Controllers\Auth\Web\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AuthController::class, 'fallback'])->name('login');

Route::prefix('auth')->controller(AuthController::class)->group(function (): void {
    Route::get('/google/redirect', 'googleRedirect');
    Route::get('/google/callback', 'googleCallback');
});
