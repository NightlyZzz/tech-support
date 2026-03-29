<?php

namespace App\Services\DTO\Ticket;

use App\Http\Requests\Ticket\UpdateTicketRequest;
use App\Models\Ticket\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

readonly class UpdateTicketDTO
{
    private User $user;
    private ?int $employeeId;
    private ?int $ticketStatusId;

    public function __construct(private Ticket $ticket, UpdateTicketRequest $request)
    {
        $this->user = Auth::user();
        $this->employeeId = $request->getEmployeeId();
        $this->ticketStatusId = $request->getTicketStatusId();
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getTicket(): Ticket
    {
        return $this->ticket;
    }

    public function getEmployeeId(): ?int
    {
        return $this->employeeId;
    }

    public function getTicketStatusId(): ?int
    {
        return $this->ticketStatusId;
    }
}
