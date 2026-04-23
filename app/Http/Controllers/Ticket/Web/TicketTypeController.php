<?php

namespace App\Http\Controllers\Ticket\Web;

use App\Http\Controllers\Controller;
use App\Http\Resources\Ticket\Type\TicketTypeCollection;
use App\Models\Ticket\TicketType;
use Illuminate\Routing\Attributes\Controllers\Middleware;
use Illuminate\Support\Facades\Cache;

#[Middleware('auth:sanctum')]
class TicketTypeController extends Controller
{
    public function all(): TicketTypeCollection
    {
        return new TicketTypeCollection(
            Cache::remember('ticket_types', 3600, fn () => TicketType::all())
        );
    }
}
