<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Services\DTO\Auth\LoginDTO;
use App\Services\DTO\Auth\RegisterDTO;
use App\Services\DTO\Response\SimpleResponse;
use Laravel\Sanctum\PersonalAccessToken;

interface AuthServiceInterface
{
    public function login(LoginDTO|RegisterDTO $dto, ?User $user = null): SimpleResponse;

    public function register(RegisterDTO $dto): SimpleResponse;

    public function logout(User $user, ?PersonalAccessToken $currentToken = null, bool $logoutFromAllDevices = false): SimpleResponse;
}
