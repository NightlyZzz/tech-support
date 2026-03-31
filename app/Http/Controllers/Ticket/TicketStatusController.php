<?php

namespace App\Http\Controllers\Ticket;

use App\Http\Controllers\Controller;
use App\Http\Resources\Ticket\Status\TicketStatusCollection;
use App\Models\Ticket\TicketStatus;
use Illuminate\Routing\Attributes\Controllers\Middleware;

#[Middleware('auth:sanctum')]
class TicketStatusController extends Controller
{
    public function all(): TicketStatusCollection
    {
        return new TicketStatusCollection(TicketStatus::all());
    }
}
