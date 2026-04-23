<?php

namespace App\Http\Controllers\Ticket\Web;

use App\Events\Ticket\TicketCreated;
use App\Events\Ticket\TicketUpdated;
use App\Http\Controllers\Controller;
use App\Http\Requests\Ticket\CreateTicketRequest;
use App\Http\Requests\Ticket\UpdateTicketRequest;
use App\Http\Resources\Ticket\TicketCollection;
use App\Http\Resources\Ticket\TicketResource;
use App\Models\Ticket\Ticket;
use App\Services\DTO\Ticket\CreateTicketDTO;
use App\Services\DTO\Ticket\UpdateTicketDTO;
use App\Services\Ticket\TicketServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Attributes\Controllers\Authorize;
use Illuminate\Routing\Attributes\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

#[Middleware('auth:sanctum')]
class TicketController extends Controller
{
    #[Authorize('view', 'ticket')]
    public function show(Ticket $ticket): TicketResource
    {
        $ticket->load(['sender', 'employee', 'type', 'status']);

        return new TicketResource($ticket);
    }

    public function showMy(TicketServiceInterface $service): TicketCollection
    {
        return new TicketCollection($service->showMy(Auth::user()));
    }

    #[Authorize('list', Ticket::class)]
    public function showAll(TicketServiceInterface $service): TicketCollection
    {
        return new TicketCollection($service->showAll(Auth::user()));
    }

    #[Authorize('create', Ticket::class)]
    public function store(CreateTicketRequest $request, TicketServiceInterface $service): JsonResponse
    {
        $ticket = $service->create(new CreateTicketDTO($request));
        $ticket->load(['sender', 'employee', 'type', 'status']);

        broadcast(new TicketCreated($ticket))->toOthers();

        return $this->respond([
            'message' => 'Заявка успешно создана',
            'data' => new TicketResource($ticket)->resolve(),
        ], Response::HTTP_CREATED);
    }

    #[Authorize('update', 'ticket')]
    public function update(Ticket $ticket, UpdateTicketRequest $request, TicketServiceInterface $service): JsonResponse
    {
        $response = $service->update(new UpdateTicketDTO($ticket, $request));

        $ticket->refresh()->load(['sender', 'employee', 'type', 'status']);

        broadcast(new TicketUpdated($ticket))->toOthers();

        return $this->respond(
            array_merge($response->getData(), [
                'data' => new TicketResource($ticket)->resolve(),
            ]),
            $response->succeeded() ? Response::HTTP_OK : Response::HTTP_FORBIDDEN
        );
    }
}
