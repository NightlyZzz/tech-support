<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\Request;

class LoginRequest extends Request
{
    protected const string EMAIL = 'email';
    protected const string PASSWORD = 'password';
    protected const string REMEMBER = 'remember';

    protected function prepareForValidation(): void
    {
        $this->merge([
            self::EMAIL => mb_strtolower(trim((string)$this->input(self::EMAIL))),
        ]);
    }

    public function rules(): array
    {
        return [
            self::EMAIL => [
                'required',
                'string',
                'email:rfc,dns',
                'max:255',
            ],
            self::PASSWORD => [
                'required',
                'string',
                'min:8',
                'max:255',
            ],
            self::REMEMBER => [
                'nullable',
                'boolean',
            ],
        ];
    }

    public function getEmail(): string
    {
        return (string)$this->input(self::EMAIL);
    }

    public function getPassword(): string
    {
        return (string)$this->input(self::PASSWORD);
    }

    public function getRemember(): bool
    {
        return (bool)$this->boolean(self::REMEMBER);
    }
}
