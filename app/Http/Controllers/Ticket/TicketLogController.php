<?php

namespace App\Http\Controllers\Ticket;

use App\Events\Ticket\TicketLogCreated;
use App\Http\Controllers\Controller;
use App\Http\Requests\Ticket\AttachTicketLogRequest;
use App\Http\Resources\Ticket\Log\TicketLogCollection;
use App\Http\Resources\Ticket\Log\TicketLogResource;
use App\Models\Ticket\Ticket;
use App\Services\DTO\Ticket\AttachTicketLogDTO;
use App\Services\Ticket\TicketServiceInterface;
use Illuminate\Routing\Attributes\Controllers\Authorize;
use Illuminate\Routing\Attributes\Controllers\Middleware;

#[Middleware('auth:sanctum')]
class TicketLogController extends Controller
{
    public function index(Ticket $ticket, TicketServiceInterface $service): TicketLogCollection
    {
        return new TicketLogCollection($service->showLogs($ticket));
    }

    #[Authorize('attachLog', 'ticket')]
    public function store(AttachTicketLogRequest $request, Ticket $ticket, TicketServiceInterface $service): TicketLogResource
    {
        $ticketLog = $service->attachLog(
            new AttachTicketLogDTO($ticket, $request)
        );

        $ticketLog->load(['sender', 'employee']);

        broadcast(new TicketLogCreated($ticket, $ticketLog))->toOthers();

        return new TicketLogResource($ticketLog);
    }
}
