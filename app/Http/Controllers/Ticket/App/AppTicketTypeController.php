<?php

namespace App\Http\Controllers\Ticket\App;

use App\Http\Controllers\Controller;
use App\Http\Resources\Ticket\Type\TicketTypeCollection;
use App\Models\Ticket\TicketType;
use Illuminate\Support\Facades\Cache;

class AppTicketTypeController extends Controller
{
    public function all(): TicketTypeCollection
    {
        return new TicketTypeCollection(
            Cache::remember('ticket_types', 3600, fn() => TicketType::all())
        );
    }
}
