<?php

namespace App\Http\Requests\Ticket;

use App\Http\Requests\Request;
use Illuminate\Validation\Rule;

class UpdateTicketRequest extends Request
{
    public const string EMPLOYEE_ID = 'employee_id';
    public const string TICKET_STATUS_ID = 'ticket_status_id';

    public function rules(): array
    {
        return [
            self::EMPLOYEE_ID => [
                'nullable',
                'integer',
                Rule::exists('users', 'id'),
            ],
            self::TICKET_STATUS_ID => [
                'nullable',
                'integer',
                Rule::exists('ticket_statuses', 'id'),
            ],
        ];
    }

    public function getEmployeeId(): ?int
    {
        $value = $this->input(self::EMPLOYEE_ID);

        return $value !== null ? (int)$value : null;
    }

    public function getTicketStatusId(): ?int
    {
        $value = $this->input(self::TICKET_STATUS_ID);

        return $value !== null ? (int)$value : null;
    }
}
