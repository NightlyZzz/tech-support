<?php

namespace App\Models;

use App\Enums\Role\RoleType;
use App\Models\Department\Department;
use App\Models\Role\Role;
use App\Models\Ticket\Ticket;
use App\Models\Ticket\TicketLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'email',
        'password',
        'first_name',
        'last_name',
        'middle_name',
        'secondary_email',
        'role_id',
        'department_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function userTickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'sender_id');
    }

    public function employeeTickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'employee_id');
    }

    public function userTicketLogs(): HasMany
    {
        return $this->hasMany(TicketLog::class, 'sender_id');
    }

    public function employeeTicketLogs(): HasMany
    {
        return $this->hasMany(TicketLog::class, 'employee_id');
    }

    public function isDefaultUser(): bool
    {
        return $this->role_id === RoleType::User->value;
    }

    public function isEmployee(bool $includeAdmin = true): bool
    {
        return $includeAdmin
            ? ($this->role_id === RoleType::Employee->value || $this->role_id === RoleType::Admin->value)
            : $this->role_id === RoleType::Employee->value;
    }

    public function isAdmin(): bool
    {
        return $this->role_id === RoleType::Admin->value;
    }

    public function getFullName(): string
    {
        return trim(implode(' ', array_filter([
            $this->last_name,
            $this->first_name,
            $this->middle_name,
        ])));
    }
}
