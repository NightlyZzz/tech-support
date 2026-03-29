<?php

namespace App\Enums\Ticket;

enum TicketStatusType: int
{
    case Pending = 1;
    case Review = 2;
    case Resolved = 3;

    public function getName(): string
    {
        return match ($this) {
            self::Pending => 'Не рассмотрен',
            self::Review => 'На рассмотрении',
            self::Resolved => 'Решен'
        };
    }
}
