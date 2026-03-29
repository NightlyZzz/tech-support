<?php

namespace App\Http\Resources\Ticket\Type;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class TicketTypeCollection extends ResourceCollection
{
    public function toArray(Request $request): array
    {
        return [
            'data' => TicketTypeResource::collection($this->collection)
        ];
    }
}
