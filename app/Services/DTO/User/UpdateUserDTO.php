<?php

namespace App\Services\DTO\User;

use App\Http\Requests\User\UpdateUserRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

readonly class UpdateUserDTO
{
    private User $user;
    private ?string $firstName;
    private ?string $lastName;
    private ?string $middleName;
    private ?string $email;
    private ?string $secondaryEmail;
    private ?string $newPassword;
    private ?int $departmentId;

    public function __construct(UpdateUserRequest $request)
    {
        /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
        $this->user = Auth::user();
        $this->firstName = $request->getFirstName();
        $this->lastName = $request->getLastName();
        $this->middleName = $request->getMiddleName();
        $this->email = $request->getEmail();
        $this->secondaryEmail = $request->getSecondaryEmail();
        $this->newPassword = $request->getNewPassword();
        $this->departmentId = $request->getDepartmentId();
    }

    public function getUser(): User
    {
        return $this->user;
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
