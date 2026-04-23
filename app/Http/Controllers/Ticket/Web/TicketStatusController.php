<?php

namespace App\Http\Controllers\Ticket\Web;

use App\Http\Controllers\Controller;
use App\Http\Resources\Ticket\Status\TicketStatusCollection;
use App\Models\Ticket\TicketStatus;
use Illuminate\Routing\Attributes\Controllers\Middleware;
use Illuminate\Support\Facades\Cache;

#[Middleware('auth:sanctum')]
class TicketStatusController extends Controller
{
    public function all(): TicketStatusCollection
    {
        return new TicketStatusCollection(
            Cache::remember('ticket_statuses', 3600, fn () => TicketStatus::all())
        );
    }
}
