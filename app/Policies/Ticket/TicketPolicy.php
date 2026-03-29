<?php

namespace App\Policies\Ticket;

use App\Models\Ticket\Ticket;
use App\Models\User;
use App\Policies\Policy;

class TicketPolicy extends Policy
{
    public function list(User $user): bool
    {
        return $user->isEmployee();
    }

    public function view(User $user, Ticket $ticket): bool
    {
        return
            $user->isAdmin()
            || $user->employeeTickets()->find($ticket->id) !== null
            || $user->userTickets()->find($ticket->id) !== null;
    }

    public function update(User $user, Ticket $ticket): bool
    {
        return
            $user->isAdmin()
            || $user->employeeTickets()->find($ticket->id) !== null
            || ($user->isEmployee() && $ticket->employee_id === null);
    }

    public function attachLog(User $user, Ticket $ticket): bool
    {
        return
            $user->isAdmin()
            || $user->employeeTickets()->find($ticket->id) !== null
            || $user->userTickets()->find($ticket->id) !== null;
    }
}
