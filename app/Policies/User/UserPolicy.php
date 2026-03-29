<?php

namespace App\Policies\User;

use App\Models\User;
use App\Policies\Policy;

class UserPolicy extends Policy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function updateAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function deleteAny(User $user): bool
    {
        return $user->isAdmin();
    }
}
