<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Department\DepartmentController;
use App\Http\Controllers\Role\RoleController;
use App\Http\Controllers\Ticket\TicketController;
use App\Http\Controllers\Ticket\TicketLogController;
use App\Http\Controllers\Ticket\TicketStatusController;
use App\Http\Controllers\Ticket\TicketTypeController;
use App\Http\Controllers\User\UserController;
use App\Models\Ticket\Ticket;
use Illuminate\Support\Facades\Route;

Route::controller(AuthController::class)->prefix('auth')->group(function (): void {
    Route::post('/login', 'login')->name('auth.login');
    Route::post('/register', 'register')->name('auth.register');
    Route::post('/logout', 'logout')->name('auth.logout');
});

Route::controller(DepartmentController::class)->prefix('department')->group(function (): void {
    Route::get('/all', 'showAll')->name('department.showAll');
});

Route::controller(RoleController::class)->prefix('role')->group(function (): void {
    Route::get('/all', 'showAll')->name('role.showAll');
});

Route::controller(UserController::class)->prefix('user')->group(function (): void {
    Route::get('/', 'show')->name('user.show');
    Route::get('/all', 'showAll')->name('user.show.all');
    Route::get('/{user}', 'showById')->name('user.showById');

    Route::put('/', 'update')->name('user.update');
    Route::put('/{user}', 'updateById')->name('user.update');

    Route::delete('/', 'destroy')->name('user.destroy');
    Route::delete('/{user}', 'destroyById')->name('user.destroyById');
});

Route::controller(TicketController::class)->prefix('ticket')->group(function (): void {
    Route::get('/my', 'showMy')->name('ticket.showMy');
    Route::get('/all', 'showAll')->name('ticket.showAll');
    Route::get('/{ticket}', 'show')->name('ticket.show');

    Route::post('/', 'store')->name('ticket.store');
    Route::post('/{ticket}/attach-log', 'attachLog')->name('ticket.attach');

    Route::put('/{ticket}', 'update')->name('ticket.update');
});

Route::controller(TicketLogController::class)->prefix('ticket/log')->group(function (): void {
    Route::get('/{ticket}', 'index')->name('ticket.log.show');
    Route::post('/{ticket}', 'store')->name('ticket.log.store');
});

Route::controller(TicketStatusController::class)->prefix('ticket/status')->group(function (): void {
    Route::get('/all', 'all')->name('ticket.status.all');
});

Route::middleware('auth:sanctum')->group(function (): void {
    Route::controller(TicketTypeController::class)->prefix('ticket/type')->group(function (): void {
        Route::get('/all', 'all')->name('ticket.type.all');
    });
});
