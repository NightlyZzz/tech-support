<?php

namespace App\Services\DTO\Auth\App;

use App\Http\Requests\Auth\App\AppRegisterRequest;

readonly class AppRegisterDTO
{
    private string $email;
    private string $password;
    private string $firstName;
    private string $lastName;
    private string $middleName;
    private int $departmentId;
    private string $deviceName;

    public function __construct(AppRegisterRequest $request)
    {
        $this->email = $request->getEmail();
        $this->password = $request->getPassword();
        $this->firstName = $request->getFirstName();
        $this->lastName = $request->getLastName();
        $this->middleName = $request->getMiddleName();
        $this->departmentId = $request->getDepartmentId();
        $this->deviceName = $request->getDeviceName();
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

    public function getDeviceName(): string
    {
        return $this->deviceName;
    }
}
