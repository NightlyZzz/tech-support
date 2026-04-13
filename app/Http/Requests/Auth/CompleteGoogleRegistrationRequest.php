<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\Request;
use Illuminate\Validation\Rule;

class CompleteGoogleRegistrationRequest extends Request
{
    protected const string DEPARTMENT_ID = 'department_id';
    protected const string PASSWORD = 'password';

    public function rules(): array
    {
        return [
            self::DEPARTMENT_ID => [
                'required',
                'integer',
                Rule::exists('departments', 'id'),
            ],
            self::PASSWORD => [
                'required',
                'string',
                'min:8',
                'max:255',
            ],
        ];
    }

    public function getDepartmentId(): int
    {
        return (int)$this->input(self::DEPARTMENT_ID);
    }

    public function getPassword(): string
    {
        return (string)$this->input(self::PASSWORD);
    }
}
