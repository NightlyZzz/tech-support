<?php

namespace App\Services\DTO\Ticket;

use App\Http\Requests\Ticket\AttachTicketLogRequest;
use App\Models\Ticket\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

readonly class AttachTicketLogDTO
{
    private User $user;
    private string $message;

    public function __construct(private Ticket $ticket, AttachTicketLogRequest $request)
    {
        $this->user = Auth::user();
        $this->message = $request->getMessage();
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getTicket(): Ticket
    {
        return $this->ticket;
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}
