<?php

namespace App\Http\Resources\Ticket\Log;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $author = $this->resource->employee ?? $this->resource->sender;

        return [
            'id' => $this->resource->id,
            'message' => $this->resource->message,
            'ticket_id' => $this->resource->ticket_id,
            'sender_id' => $this->resource->sender_id,
            'employee_id' => $this->resource->employee_id,
            'created_at' => $this->resource->created_at,
            'sender_name' => $this->resource->sender?->getFullName() ?? 'Удалённый пользователь',
            'employee_name' => $this->resource->employee?->getFullName(),
            'author_name' => $author?->getFullName() ?? 'Удалённый пользователь',
            'is_employee_message' => $this->resource->employee_id !== null,
        ];
    }
}
