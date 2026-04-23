<?php

namespace App\Services\Ticket;

use App\Enums\Role\RoleType;
use App\Enums\Ticket\TicketStatusType;
use App\Models\Ticket\Ticket;
use App\Models\Ticket\TicketLog;
use App\Models\User;
use App\Services\DTO\Response\SimpleResponse;
use App\Services\DTO\Ticket\AttachTicketLogDTO;
use App\Services\DTO\Ticket\CreateTicketDTO;
use App\Services\DTO\Ticket\UpdateTicketDTO;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

final class TicketService implements TicketServiceInterface
{
    private const int PER_PAGE = 15;

    public function showMy(User $user, ?int $statusId = null): LengthAwarePaginator
    {
        $query = $user->role_id === RoleType::User->value
            ? $user->userTickets()
            : $user->employeeTickets();

        return $query
            ->with(['sender', 'employee', 'type', 'status'])
            ->when($statusId !== null && $statusId > 0, function ($builder) use ($statusId): void {
                $builder->where('ticket_status_id', $statusId);
            })
            ->orderByDesc('created_at')
            ->paginate(self::PER_PAGE);
    }

    public function showAll(User $user, ?int $statusId = null): LengthAwarePaginator
    {
        $query = Ticket::query()->with(['sender', 'employee', 'type', 'status']);

        if ($user->role_id !== RoleType::Admin->value) {
            $query->whereNull('employee_id');
        }

        return $query
            ->when($statusId !== null && $statusId > 0, function ($builder) use ($statusId): void {
                $builder->where('ticket_status_id', $statusId);
            })
            ->orderByDesc('created_at')
            ->paginate(self::PER_PAGE);
    }

    public function create(CreateTicketDTO $dto): Ticket
    {
        return Ticket::query()->create([
            'sender_id' => $dto->getUser()->id,
            'description' => $dto->getDescription(),
            'contact_phone' => $dto->getContactPhone(),
            'ticket_type_id' => $dto->getTicketTypeId(),
            'ticket_status_id' => TicketStatusType::Pending->value,
        ]);
    }

    public function showLogs(Ticket $ticket): Collection
    {
        return $ticket
            ->logs()
            ->with(['ticket', 'sender', 'employee'])
            ->orderBy('created_at')
            ->get();
    }

    public function update(UpdateTicketDTO $dto): SimpleResponse
    {
        $ticket = $dto->getTicket();
        $data = [];

        if (($employeeId = $dto->getEmployeeId()) !== null) {
            if ($ticket->employee_id === null || $dto->getUser()->role_id === RoleType::Admin->value) {
                $data['employee_id'] = $employeeId;
                $data['ticket_status_id'] = TicketStatusType::Review->value;
            }
        }

        if (($ticketStatusId = $dto->getTicketStatusId()) !== null) {
            $data['ticket_status_id'] = $ticketStatusId;

            if ($ticketStatusId === TicketStatusType::Pending->value) {
                $data['employee_id'] = null;
            }
        }

        if ($data === []) {
            return new SimpleResponse(true, [
                'message' => 'Нет данных для обновления заявки',
            ]);
        }

        $ticket->update($data);

        return new SimpleResponse(true, [
            'message' => 'Данные заявки были успешно обновлены',
        ]);
    }

    public function attachLog(AttachTicketLogDTO $dto): TicketLog
    {
        $user = $dto->getUser();

        return TicketLog::query()->create([
            'message' => $dto->getMessage(),
            'ticket_id' => $dto->getTicket()->id,
            $user->isEmployee() ? 'employee_id' : 'sender_id' => $user->id,
        ]);
    }
}
