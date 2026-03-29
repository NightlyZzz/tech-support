<?php

namespace App\Services\DTO\Auth;

use App\Http\Requests\Auth\RegisterRequest;

readonly class RegisterDTO
{
    private string $email;
    private string $password;
    private string $firstName;
    private string $lastName;
    private string $middleName;
    private int $departmentId;
    private bool $remember;

    public function __construct(RegisterRequest $request)
    {
        $this->email = $request->getEmail();
        $this->password = $request->getPassword();
        $this->firstName = $request->getFirstName();
        $this->lastName = $request->getLastName();
        $this->middleName = $request->getMiddleName();
        $this->departmentId = $request->getDepartmentId();
        $this->remember = $request->getRemember();
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getMiddleName(): string
    {
        return $this->middleName;
    }

    public function getDepartmentId(): int
    {
        return $this->departmentId;
    }

    public function isRemember(): bool
    {
        return $this->remember;
    }
}
