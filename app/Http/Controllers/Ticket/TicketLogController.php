<?php

namespace App\Http\Controllers\Ticket;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ticket\AttachTicketLogRequest;
use App\Http\Resources\Ticket\Log\TicketLogCollection;
use App\Http\Resources\Ticket\Log\TicketLogResource;
use App\Models\Ticket\Ticket;
use App\Services\DTO\Ticket\AttachTicketLogDTO;
use App\Services\Ticket\TicketServiceInterface;

class TicketLogController extends Controller
{
    public function index(Ticket $ticket, TicketServiceInterface $service): TicketLogCollection
    {
        return new TicketLogCollection($service->showLogs($ticket));
    }

    public function store(AttachTicketLogRequest $request, Ticket $ticket, TicketServiceInterface $service): TicketLogResource
    {
        return new TicketLogResource($service->attachLog(
            new AttachTicketLogDTO($ticket, $request)
        ));
    }
}
