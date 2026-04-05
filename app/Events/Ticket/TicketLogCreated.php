<?php

namespace App\Events\Ticket;

use App\Http\Resources\Ticket\Log\TicketLogResource;
use App\Models\Ticket\Ticket;
use App\Models\Ticket\TicketLog;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketLogCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Ticket $ticket,
        public TicketLog $ticketLog
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('ticket.' . $this->ticket->id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'ticket.log.created';
    }

    public function broadcastWith(): array
    {
        return new TicketLogResource($this->ticketLog)->resolve();
    }
}
