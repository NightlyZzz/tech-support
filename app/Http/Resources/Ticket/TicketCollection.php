<?php

namespace App\Http\Resources\Ticket;

use App\Http\Resources\Concerns\HasPaginationMeta;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class TicketCollection extends ResourceCollection
{
    use HasPaginationMeta;

    public function toArray(Request $request): array
    {
        return [
            'data' => TicketResource::collection($this->collection),
        ];
    }
}
