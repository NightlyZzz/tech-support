<?php

namespace App\Services\User;

use App\Models\User;
use App\Services\DTO\Response\SimpleResponse;
use App\Services\DTO\User\AdminUpdateUserDTO;
use App\Services\DTO\User\UpdateUserDTO;

interface UserServiceInterface
{
    public function update(UpdateUserDTO|AdminUpdateUserDTO $dto): SimpleResponse;

    public function delete(User $user): SimpleResponse;
}
