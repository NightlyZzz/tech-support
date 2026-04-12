<?php

namespace Database\Factories;

use App\Enums\Role\RoleType;
use App\Models\Department\Department;
use App\Models\Role\Role;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected static ?string $password = null;

    public function definition(): array
    {
        return [
            'email' => fake()->unique()->userName() . '@gmail.com',
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password123'),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'middle_name' => fake()->firstName(),
            'secondary_email' => fake()->boolean(30)
                ? fake()->unique()->userName() . '@outlook.com'
                : null,
            'remember_token' => Str::random(10),
            'role_id' => Role::query()->find(RoleType::User->value)?->id,
            'department_id' => Department::query()->value('id'),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn(): array => [
            'email_verified_at' => null,
        ]);
    }

    public function withRole(RoleType $roleType): static
    {
        return $this->state(fn(): array => [
            'role_id' => Role::query()->find($roleType->value)?->id,
        ]);
    }

    public function withoutDepartment(): static
    {
        return $this->state(fn(): array => [
            'department_id' => null,
        ]);
    }
}
