<?php

namespace App\Enums\Role;

enum RoleType: int
{
    case User = 1;
    case Employee = 2;
    case Admin = 3;

    public function getName(): string
    {
        return match ($this) {
            self::User => 'Пользователь',
            self::Employee => 'Сотрудник',
            self::Admin => 'Администратор'
        };
    }
}
