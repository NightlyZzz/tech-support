<?php

namespace App\Http\Controllers\Ticket;

use App\Http\Controllers\Controller;
use App\Http\Resources\Ticket\Type\TicketTypeCollection;
use App\Models\Ticket\TicketType;

class TicketTypeController extends Controller
{
    public function all(): TicketTypeCollection
    {
        return new TicketTypeCollection(TicketType::all());
    }
}
