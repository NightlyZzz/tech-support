<?php

namespace App\Http\Requests\Ticket;

use App\Http\Requests\Request;
use Illuminate\Validation\Rule;

class CreateTicketRequest extends Request
{
    protected const string DESCRIPTION = 'description';
    protected const string CONTACT_PHONE = 'contact_phone';
    protected const string TICKET_TYPE_ID = 'ticket_type_id';

    protected function prepareForValidation(): void
    {
        $this->merge([
            self::DESCRIPTION => trim((string)$this->input(self::DESCRIPTION)),
            self::CONTACT_PHONE => preg_replace('/\D+/', '', (string)$this->input(self::CONTACT_PHONE)),
        ]);
    }

    public function rules(): array
    {
        return [
            self::DESCRIPTION => [
                'required',
                'string',
                'min:3',
                'max:1000',
            ],
            self::CONTACT_PHONE => [
                'required',
                'string',
                'min:10',
                'max:15',
            ],
            self::TICKET_TYPE_ID => [
                'required',
                'integer',
                Rule::exists('ticket_types', 'id'),
            ],
        ];
    }

    public function getDescription(): string
    {
        return (string)$this->input(self::DESCRIPTION);
    }

    public function getContactPhone(): string
    {
        return (string)$this->input(self::CONTACT_PHONE);
    }

    public function getTicketTypeId(): int
    {
        return (int)$this->input(self::TICKET_TYPE_ID);
    }
}
