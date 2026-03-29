<?php

namespace App\Services\DTO\Ticket;

use App\Http\Requests\Ticket\CreateTicketRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

readonly class CreateTicketDTO
{
    private User $user;
    private string $description;
    private string $contactPhone;
    private int $ticketTypeId;

    public function __construct(CreateTicketRequest $request)
    {
        /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
        $this->user = Auth::user();
        $this->description = $request->getDescription();
        $this->contactPhone = $request->getContactPhone();
        $this->ticketTypeId = $request->getTicketTypeId();
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getContactPhone(): string
    {
        return $this->contactPhone;
    }

    public function getTicketTypeId(): int
    {
        return $this->ticketTypeId;
    }
}
