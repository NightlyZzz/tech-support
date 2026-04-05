<?php

namespace App\Services\User;

use App\Models\User;
use App\Services\DTO\Response\SimpleResponse;
use App\Services\DTO\User\AdminUpdateUserDTO;
use App\Services\DTO\User\UpdateUserDTO;
use Illuminate\Pagination\LengthAwarePaginator;

interface UserServiceInterface
{
    public function showAll(User $user, ?string $searchQuery = null): LengthAwarePaginator;

    public function update(UpdateUserDTO|AdminUpdateUserDTO $dto): SimpleResponse;

    public function delete(User $user): SimpleResponse;
}
