<?php

namespace App\Http\Requests\User;

use App\Http\Requests\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AdminUpdateUserRequest extends Request
{
    protected const string FIRST_NAME = 'first_name';
    protected const string LAST_NAME = 'last_name';
    protected const string MIDDLE_NAME = 'middle_name';
    protected const string EMAIL = 'email';
    protected const string SECONDARY_EMAIL = 'secondary_email';
    protected const string NEW_PASSWORD = 'new_password';
    protected const string DEPARTMENT_ID = 'department_id';
    protected const string ROLE_ID = 'role_id';

    public function rules(): array
    {
        $userId = $this->route('user')?->id;

        return [
            self::FIRST_NAME => [
                'nullable',
                'string',
                'max:255'
            ],
            self::LAST_NAME => [
                'nullable',
                'string',
                'max:255'
            ],
            self::MIDDLE_NAME => [
                'nullable',
                'string',
                'max:255'
            ],
            self::EMAIL => [
                'nullable',
                'email',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            self::SECONDARY_EMAIL => [
                'nullable',
                'email',
                Rule::unique('users', 'secondary_email')->ignore($userId),
            ],
            self::NEW_PASSWORD => [
                'nullable',
                'string',
                'min:8'
            ],
            self::DEPARTMENT_ID => [
                'nullable',
                'integer',
                Rule::exists('departments', 'id')
            ],
            self::ROLE_ID => [
                'nullable',
                'integer',
                Rule::exists('roles', 'id')
            ],
        ];
    }

    public function getFirstName(): ?string
    {
        return $this->input(self::FIRST_NAME);
    }

    public function getLastName(): ?string
    {
        return $this->input(self::LAST_NAME);
    }

    public function getMiddleName(): ?string
    {
        return $this->input(self::MIDDLE_NAME);
    }

    public function getEmail(): ?string
    {
        return $this->input(self::EMAIL);
    }

    public function getSecondaryEmail(): ?string
    {
        return $this->input(self::SECONDARY_EMAIL);
    }

    public function getNewPassword(): ?string
    {
        return $this->input(self::NEW_PASSWORD);
    }

    public function getDepartmentId(): ?int
    {
        return $this->input(self::DEPARTMENT_ID);
    }

    public function getRoleId(): ?int
    {
        return $this->input(self::ROLE_ID);
    }
}
