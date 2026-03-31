<?php

namespace App\Http\Controllers\Ticket;

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
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Routing\Attributes\Controllers\Middleware;
use Illuminate\Routing\Attributes\Controllers\Authorize;

#[Middleware('auth:sanctum')]
class TicketController extends Controller
{
    #[Authorize('view', 'ticket')]
    public function show(Ticket $ticket): TicketResource
    {
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

    public function store(CreateTicketRequest $request, TicketServiceInterface $service): JsonResponse
    {
        $response = $service->create(new CreateTicketDTO($request));
        return $this->respond(
            $response->getData(),
            $response->succeeded() ? Response::HTTP_OK : Response::HTTP_UNAUTHORIZED
        );
    }

    #[Authorize('update', 'ticket')]
    public function update(Ticket $ticket, UpdateTicketRequest $request, TicketServiceInterface $service): JsonResponse
    {
        $response = $service->update(new UpdateTicketDTO($ticket, $request));
        return $this->respond(
            $response->getData(),
            $response->succeeded() ? Response::HTTP_OK : Response::HTTP_FORBIDDEN
        );
    }
}
