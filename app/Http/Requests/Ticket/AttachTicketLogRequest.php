<?php

namespace App\Http\Requests\Ticket;

use App\Http\Requests\Request;

class AttachTicketLogRequest extends Request
{
    public const string MESSAGE = 'message';

    public function rules(): array
    {
        return [
            self::MESSAGE => [
                'required',
                'string',
                'min:1'
            ]
        ];
    }

    public function getMessage(): string
    {
        return $this->input(self::MESSAGE);
    }
}
