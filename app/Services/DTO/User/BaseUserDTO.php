<?php

namespace App\Services\DTO\User;

abstract readonly class BaseUserDTO
{
    public function __construct(
        protected ?string $firstName,
        protected ?string $lastName,
        protected ?string $middleName,
        protected ?string $email,
        protected ?string $secondaryEmail,
        protected ?string $newPassword,
        protected ?int $departmentId
    ) {
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function getMiddleName(): ?string
    {
        return $this->middleName;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getSecondaryEmail(): ?string
    {
        return $this->secondaryEmail;
    }

    public function getNewPassword(): ?string
    {
        return $this->newPassword;
    }

    public function getDepartmentId(): ?int
    {
        return $this->departmentId;
    }
}
