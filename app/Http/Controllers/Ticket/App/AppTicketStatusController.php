<?php

namespace App\Http\Controllers\Ticket\App;

use App\Http\Controllers\Controller;
use App\Http\Resources\Ticket\Status\TicketStatusCollection;
use App\Models\Ticket\TicketStatus;
use Illuminate\Support\Facades\Cache;

class AppTicketStatusController extends Controller
{
    public function all(): TicketStatusCollection
    {
        return new TicketStatusCollection(
            Cache::remember('ticket_statuses', 3600, fn() => TicketStatus::all())
        );
    }
}
