<?php

namespace App\Http\Controllers\Ticket\App;

use App\Events\Ticket\TicketLogCreated;
use App\Http\Controllers\Controller;
use App\Http\Requests\Ticket\AttachTicketLogRequest;
use App\Http\Resources\Ticket\Log\TicketLogCollection;
use App\Http\Resources\Ticket\Log\TicketLogResource;
use App\Models\Ticket\Ticket;
use App\Services\DTO\Ticket\AttachTicketLogDTO;
use App\Services\Ticket\TicketServiceInterface;
use Illuminate\Routing\Attributes\Controllers\Authorize;

class AppTicketLogController extends Controller
{
    #[Authorize('view', 'ticket')]
    public function index(Ticket $ticket, TicketServiceInterface $service): TicketLogCollection
    {
        return new TicketLogCollection($service->showLogs($ticket));
    }

    #[Authorize('attachLog', 'ticket')]
    public function store(AttachTicketLogRequest $request, Ticket $ticket, TicketServiceInterface $service): TicketLogResource
    {
        $ticketLog = $service->attachLog(new AttachTicketLogDTO($ticket, $request));
        $ticketLog->load(['ticket', 'sender', 'employee']);

        broadcast(new TicketLogCreated($ticket, $ticketLog))->toOthers();

        return new TicketLogResource($ticketLog);
    }
}
