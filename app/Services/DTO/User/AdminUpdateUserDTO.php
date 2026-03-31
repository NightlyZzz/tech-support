<?php

namespace App\Services\DTO\User;

use App\Http\Requests\User\AdminUpdateUserRequest;
use App\Models\User;

readonly class AdminUpdateUserDTO extends BaseUserDTO
{
    private User $user;
    private ?int $roleId;

    public function __construct(User $user, AdminUpdateUserRequest $request)
    {
        $this->user = $user;
        $this->roleId = $request->getRoleId();

        parent::__construct(
            $request->getFirstName(),
            $request->getLastName(),
            $request->getMiddleName(),
            $request->getEmail(),
            $request->getSecondaryEmail(),
            $request->getNewPassword(),
            $request->getDepartmentId()
        );
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getRoleId(): ?int
    {
        return $this->roleId;
    }
}
