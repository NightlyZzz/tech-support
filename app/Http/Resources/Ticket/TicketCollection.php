<?php

namespace App\Http\Resources\Ticket;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class TicketCollection extends ResourceCollection
{
    public function toArray(Request $request): array
    {
        return [
            'data' => TicketResource::collection($this->collection),
            'meta' => [
                'current_page' => $this->currentPage(),
                'last_page' => $this->lastPage(),
                'per_page' => $this->perPage(),
                'total' => $this->total()
            ]
        ];
    }
}
