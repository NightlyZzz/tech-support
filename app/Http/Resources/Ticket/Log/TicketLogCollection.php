<?php

namespace App\Http\Resources\Ticket\Log;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class TicketLogCollection extends ResourceCollection
{
    public function toArray(Request $request): array
    {
        return [
            'data' => TicketLogResource::collection($this->collection)
        ];
    }
}
