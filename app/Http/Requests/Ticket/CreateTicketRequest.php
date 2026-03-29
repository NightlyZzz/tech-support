<?php

namespace App\Http\Requests\Ticket;

use App\Http\Requests\Request;

class CreateTicketRequest extends Request
{
    protected const string DESCRIPTION = 'description';
    protected const string CONTACT_PHONE = 'contact_phone';
    protected const string TICKET_TYPE_ID  = 'ticket_type_id';

    public function rules(): array
    {
        return [
            self::DESCRIPTION => [
                'required',
                'string',
                'max:255'
            ],
            self::CONTACT_PHONE => [
                'required',
                'string',
                'min:10',
                'max:12'
            ],
            self::TICKET_TYPE_ID => [
                'required',
                'integer'
            ]
        ];
    }

    public function getDescription(): string
    {
        return $this->input(self::DESCRIPTION);
    }

    public function getContactPhone(): string
    {
        return $this->input(self::CONTACT_PHONE);
    }

    public function getTicketTypeId(): int
    {
        return $this->input(self::TICKET_TYPE_ID);
    }
}
