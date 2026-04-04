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

Route::controller(AuthController::class)->prefix('auth')->group(function (): void {
    Route::post('/login', 'login');
    Route::post('/register', 'register');
    Route::post('/logout', 'logout')->middleware('auth:sanctum');
});

Route::prefix('public')->group(function () {
    Route::get('/departments', [DepartmentController::class, 'showAll']);
    Route::get('/roles', [RoleController::class, 'showAll']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::controller(UserController::class)->prefix('user')->group(function (): void {
        Route::get('/', 'show');
        Route::get('/all', 'showAll');
        Route::get('/{user}', 'showById');

        Route::put('/', 'update');
        Route::put('/{user}', 'updateById');

        Route::delete('/', 'destroy');
        Route::delete('/{user}', 'destroyById');
    });

    Route::controller(TicketController::class)->prefix('ticket')->group(function (): void {
        Route::get('/my', 'showMy');
        Route::get('/all', 'showAll');
        Route::get('/{ticket}', 'show');

        Route::post('/', 'store');
        Route::post('/{ticket}/attach-log', 'attachLog');

        Route::put('/{ticket}', 'update');
    });

    Route::controller(TicketLogController::class)->prefix('ticket/log')->group(function (): void {
        Route::get('/{ticket}', 'index');
        Route::post('/{ticket}', 'store');
    });

    Route::controller(TicketStatusController::class)->prefix('ticket/status')->group(function (): void {
        Route::get('/all', 'all');
    });

    Route::controller(TicketTypeController::class)->prefix('ticket/type')->group(function (): void {
        Route::get('/all', 'all');
    });

});
