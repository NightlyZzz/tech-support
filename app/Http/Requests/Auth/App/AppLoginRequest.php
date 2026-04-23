<?php

namespace App\Http\Requests\Auth\App;

use App\Http\Requests\Request;

class AppLoginRequest extends Request
{
    protected const string EMAIL = 'email';
    protected const string PASSWORD = 'password';
    protected const string DEVICE_NAME = 'device_name';

    protected function prepareForValidation(): void
    {
        $this->merge([
            self::EMAIL => mb_strtolower(trim((string)$this->input(self::EMAIL))),
            self::DEVICE_NAME => trim((string)$this->input(self::DEVICE_NAME)),
        ]);
    }

    public function rules(): array
    {
        return [
            self::EMAIL => [
                'required',
                'string',
                'email:rfc',
                'max:255',
            ],
            self::PASSWORD => [
                'required',
                'string',
                'min:8',
                'max:255',
            ],
            self::DEVICE_NAME => [
                'required',
                'string',
                'max:255',
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

    public function getDeviceName(): string
    {
        return (string)$this->input(self::DEVICE_NAME);
    }
}
