<?php

namespace App\Services\Ticket;

use App\Models\Ticket\Ticket;
use App\Models\Ticket\TicketLog;
use App\Models\User;
use App\Services\DTO\Response\SimpleResponse;
use App\Services\DTO\Ticket\AttachTicketLogDTO;
use App\Services\DTO\Ticket\CreateTicketDTO;
use App\Services\DTO\Ticket\UpdateTicketDTO;
use Illuminate\Pagination\LengthAwarePaginator;

interface TicketServiceInterface
{
    public function showMy(User $user): LengthAwarePaginator;

    public function showAll(User $user): LengthAwarePaginator;

    public function create(CreateTicketDTO $dto): Ticket;

    public function update(UpdateTicketDTO $dto): SimpleResponse;

    public function attachLog(AttachTicketLogDTO $dto): TicketLog;
}
