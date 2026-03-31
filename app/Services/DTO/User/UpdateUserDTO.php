<?php

namespace App\Services\DTO\User;

use App\Http\Requests\User\UpdateUserRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

readonly class UpdateUserDTO extends BaseUserDTO
{
    private User $user;

    public function __construct(UpdateUserRequest $request)
    {
        $this->user = Auth::user();

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
}
