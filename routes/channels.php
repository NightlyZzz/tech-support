<?php

use App\Models\Ticket\Ticket;
use Illuminate\Support\Facades\Broadcast;

Broadcast::routes([
    'middleware' => ['auth:sanctum'],
]);

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    if ($user->isAdmin()) {
        return true;
    }

    return (int)$user->id === (int)$id;
});

Broadcast::channel('users.all', function ($user) {
    return $user->isAdmin();
});

Broadcast::channel('tickets.all', function ($user) {
    return $user->isEmployee();
});

Broadcast::channel('ticket.{ticketId}', function ($user, int $ticketId) {
    $ticket = Ticket::find($ticketId);

    if (!$ticket) {
        return false;
    }

    if ($user->isAdmin()) {
        return true;
    }

    if ((int)$ticket->sender_id === (int)$user->id) {
        return true;
    }

    if ($ticket->employee_id !== null && (int)$ticket->employee_id === (int)$user->id) {
        return true;
    }

    return false;
});
