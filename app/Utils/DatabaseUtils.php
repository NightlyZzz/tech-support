<?php

namespace App\Utils;

use Illuminate\Support\Facades\DB;

final class DatabaseUtils
{
    public static function insertNamedEnums(string $enumClassName, string $table): void
    {
        $table = DB::table($table);
        foreach ($enumClassName::cases() as $enum) {
            $table->insert([
                'id' => $enum->value,
                'name' => $enum->getName()
            ]);
        }
    }
}
