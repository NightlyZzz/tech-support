<?php

namespace App\Http\Requests\Auth\Web;

use App\Http\Requests\Request;
use Illuminate\Validation\Rule;

class RegisterRequest extends Request
{
    protected const string EMAIL = 'email';
    protected const string PASSWORD = 'password';
    protected const string FIRST_NAME = 'first_name';
    protected const string LAST_NAME = 'last_name';
    protected const string MIDDLE_NAME = 'middle_name';
    protected const string DEPARTMENT_ID = 'department_id';
    protected const string REMEMBER = 'remember';

    protected function prepareForValidation(): void
    {
        $this->merge([
            self::EMAIL => mb_strtolower(trim((string)$this->input(self::EMAIL))),
            self::FIRST_NAME => trim((string)$this->input(self::FIRST_NAME)),
            self::LAST_NAME => trim((string)$this->input(self::LAST_NAME)),
            self::MIDDLE_NAME => trim((string)$this->input(self::MIDDLE_NAME)),
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
                Rule::unique('users', 'email'),
            ],
            self::PASSWORD => [
                'required',
                'string',
                'min:8',
                'max:255',
            ],
            self::FIRST_NAME => [
                'required',
                'string',
                'max:255',
            ],
            self::LAST_NAME => [
                'required',
                'string',
                'max:255',
            ],
            self::MIDDLE_NAME => [
                'required',
                'string',
                'max:255',
            ],
            self::DEPARTMENT_ID => [
                'required',
                'integer',
                Rule::exists('departments', 'id'),
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

    public function getFirstName(): string
    {
        return (string)$this->input(self::FIRST_NAME);
    }

    public function getLastName(): string
    {
        return (string)$this->input(self::LAST_NAME);
    }

    public function getMiddleName(): string
    {
        return (string)$this->input(self::MIDDLE_NAME);
    }

    public function getDepartmentId(): int
    {
        return (int)$this->input(self::DEPARTMENT_ID);
    }

    public function getRemember(): bool
    {
        return (bool)$this->boolean(self::REMEMBER);
    }
}
