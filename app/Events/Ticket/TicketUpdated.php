<?php

namespace App\Events\Ticket;

use App\Http\Resources\Ticket\TicketResource;
use App\Models\Ticket\Ticket;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Ticket $ticket
    ) {}

    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel('ticket.' . $this->ticket->id),
            new PrivateChannel('tickets.all'),
            new PrivateChannel('App.Models.User.' . $this->ticket->sender_id),
        ];

        if ($this->ticket->employee_id !== null) {
            $channels[] = new PrivateChannel('App.Models.User.' . $this->ticket->employee_id);
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'ticket.updated';
    }

    public function broadcastWith(): array
    {
        return new TicketResource($this->ticket)->resolve();
    }
}
