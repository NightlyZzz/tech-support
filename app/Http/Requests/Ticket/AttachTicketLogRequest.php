<?php

namespace App\Http\Requests\Ticket;

use App\Http\Requests\Request;

class AttachTicketLogRequest extends Request
{
    public const string MESSAGE = 'message';

    protected function prepareForValidation(): void
    {
        $this->merge([
            self::MESSAGE => trim((string)$this->input(self::MESSAGE)),
        ]);
    }

    public function rules(): array
    {
        return [
            self::MESSAGE => [
                'required',
                'string',
                'min:1',
                'max:2000',
            ],
        ];
    }

    public function getMessage(): string
    {
        return (string)$this->input(self::MESSAGE);
    }
}
