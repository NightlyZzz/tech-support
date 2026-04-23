<?php

use App\Http\Controllers\Auth\App\AuthController as AppAuthController;
use App\Http\Controllers\Role\RoleController;
use App\Http\Controllers\Ticket\App\AppTicketController;
use App\Http\Controllers\Ticket\App\AppTicketLogController;
use App\Http\Controllers\Ticket\App\AppTicketStatusController;
use App\Http\Controllers\Ticket\App\AppTicketTypeController;
use App\Http\Controllers\User\App\AppUserController;
use Illuminate\Support\Facades\Route;

Route::prefix('app')->group(function (): void {
    Route::prefix('auth')->controller(AppAuthController::class)->group(function (): void {
        Route::post('/login', 'login')->middleware('throttle:auth-app-login');
        Route::post('/register', 'register')->middleware('throttle:auth-app-register');

        Route::middleware(['auth:sanctum', 'auth.token'])->group(function (): void {
            Route::post('/logout', 'logout');
            Route::post('/logout-all', 'logoutAll');
        });
    });

    Route::middleware(['auth:sanctum', 'auth.token'])->group(function (): void {
        Route::prefix('user')->controller(AppUserController::class)->group(function (): void {
            Route::get('/', 'show');
            Route::get('/all', 'showAll');
            Route::get('/{user}', 'showById');
            Route::put('/', 'update');
            Route::put('/{user}', 'updateById');
            Route::delete('/', 'destroy');
            Route::delete('/{user}', 'destroyById');
        });

        Route::prefix('role')->controller(RoleController::class)->group(function (): void {
            Route::get('/all', 'showAll');
        });

        Route::prefix('ticket')->controller(AppTicketController::class)->group(function (): void {
            Route::get('/my', 'showMy');
            Route::get('/all', 'showAll');
            Route::get('/{ticket}', 'show');
            Route::post('/', 'store');
            Route::put('/{ticket}', 'update');
        });

        Route::prefix('ticket/log')->controller(AppTicketLogController::class)->group(function (): void {
            Route::get('/{ticket}', 'index');
            Route::post('/{ticket}', 'store');
        });

        Route::prefix('ticket/status')->controller(AppTicketStatusController::class)->group(function (): void {
            Route::get('/all', 'all');
        });

        Route::prefix('ticket/type')->controller(AppTicketTypeController::class)->group(function (): void {
            Route::get('/all', 'all');
        });
    });
});
