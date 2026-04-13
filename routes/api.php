<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Department\DepartmentController;
use App\Http\Controllers\Role\RoleController;
use App\Http\Controllers\Ticket\TicketController;
use App\Http\Controllers\Ticket\TicketLogController;
use App\Http\Controllers\Ticket\TicketStatusController;
use App\Http\Controllers\Ticket\TicketTypeController;
use App\Http\Controllers\User\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->controller(AuthController::class)->group(function (): void {
    Route::post('/login', 'login')->middleware('throttle:auth-login');
    Route::post('/register', 'register')->middleware('throttle:auth-register');
    Route::post('/logout', 'logout')->middleware('auth:sanctum');
    Route::post('/google/complete-registration', 'completeGoogleRegistration')->middleware('auth:sanctum');
});

Route::prefix('public')->group(function (): void {
    Route::get('/departments', [DepartmentController::class, 'showAll']);
    Route::get('/roles', [RoleController::class, 'showAll']);
});

Route::middleware('auth:sanctum')->group(function (): void {
    Route::prefix('user')->controller(UserController::class)->group(function (): void {
        Route::get('/', 'show');
        Route::get('/all', 'showAll');
        Route::get('/{user}', 'showById');

        Route::put('/', 'update');
        Route::put('/{user}', 'updateById');

        Route::delete('/', 'destroy');
        Route::delete('/{user}', 'destroyById');
    });

    Route::prefix('ticket')->controller(TicketController::class)->group(function (): void {
        Route::get('/my', 'showMy');
        Route::get('/all', 'showAll');
        Route::get('/{ticket}', 'show');

        Route::post('/', 'store');
        Route::post('/{ticket}/attach-log', 'attachLog');

        Route::put('/{ticket}', 'update');
    });

    Route::prefix('ticket/log')->controller(TicketLogController::class)->group(function (): void {
        Route::get('/{ticket}', 'index');
        Route::post('/{ticket}', 'store');
    });

    Route::prefix('ticket/status')->controller(TicketStatusController::class)->group(function (): void {
        Route::get('/all', 'all');
    });

    Route::prefix('ticket/type')->controller(TicketTypeController::class)->group(function (): void {
        Route::get('/all', 'all');
    });
});
