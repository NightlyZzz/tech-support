<?php

namespace App\Services\Ticket;

use App\Models\Ticket\TicketLog;
use App\Models\User;
use App\Services\DTO\Response\SimpleResponse;
use App\Services\DTO\Ticket\AttachTicketLogDTO;
use App\Services\DTO\Ticket\CreateTicketDTO;
use App\Services\DTO\Ticket\UpdateTicketDTO;
use Illuminate\Support\Collection;

interface TicketServiceInterface
{
    public function showMy(User $user): Collection;

    public function showAll(User $user): Collection;

    public function create(CreateTicketDTO $dto): SimpleResponse;

    public function update(UpdateTicketDTO $dto): SimpleResponse;

    public function attachLog(AttachTicketLogDTO $dto): TicketLog;
}
