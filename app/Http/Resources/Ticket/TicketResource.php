<?php

namespace App\Http\Resources\Ticket;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'description' => $this->resource->description,
            'contact_phone' => $this->resource->contact_phone,
            'sender_id' => $this->resource->sender_id,
            'employee_id' => $this->resource->employee_id,
            'type_id' => $this->resource->type?->id,
            'type_name' => $this->resource->type?->name,
            'status_id' => $this->resource->status?->id,
            'status_name' => $this->resource->status?->name,
            'created_at' => $this->resource->created_at,
            'sender_name' => $this->resource->sender?->getFullName() ?? 'Удалённый пользователь',
            'employee_name' => $this->resource->employee?->getFullName(),
        ];
    }
}
