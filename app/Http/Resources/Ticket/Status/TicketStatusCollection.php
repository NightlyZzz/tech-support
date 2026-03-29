<?php

namespace App\Http\Resources\Ticket\Status;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class TicketStatusCollection extends ResourceCollection
{
    public function toArray(Request $request): array
    {
        return [
            'data' => TicketStatusResource::collection($this->collection)
        ];
    }
}
