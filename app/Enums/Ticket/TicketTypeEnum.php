<?php

namespace App\Enums\Ticket;

enum TicketTypeEnum: int
{
    case SetupSoftware = 1;
    case InstallSoftware = 2;
    case SupportEdu = 3;
    case SupportSreda = 4;

    public function getName(): string
    {
        return match ($this) {
            self::SetupSoftware => 'Настройка программ',
            self::InstallSoftware => 'Установка программ',
            self::SupportEdu => 'Тех. поддержка "edu.amchs.ru"',
            self::SupportSreda => 'Тех. поддержка "Среда"'
        };
    }

}
