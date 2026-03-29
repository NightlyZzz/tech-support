<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\Request;
use Illuminate\Validation\Rule;

class LoginRequest extends Request
{
    protected const string EMAIL = 'email';
    protected const string PASSWORD = 'password';
    protected const string REMEMBER = 'remember';

    public function rules(): array
    {
        return [
            self::EMAIL => [
                'required',
                'email',
                Rule::exists('users', 'email')
            ],
            self::PASSWORD => [
                'required',
                'string',
                'min:8'
            ],
            self::REMEMBER => [
                'nullable',
                'boolean'
            ]
        ];
    }

    public function getEmail(): string
    {
        return $this->input(self::EMAIL);
    }

    public function getPassword(): string
    {
        return $this->input(self::PASSWORD);
    }

    public function getRemember(): bool
    {
        return (bool)$this->input(self::REMEMBER);
    }
}
