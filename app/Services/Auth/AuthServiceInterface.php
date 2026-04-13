<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Services\DTO\Auth\LoginDTO;
use App\Services\DTO\Auth\RegisterDTO;
use App\Services\DTO\Response\SimpleResponse;

interface AuthServiceInterface
{
    public function login(LoginDTO $dto): SimpleResponse;

    public function register(RegisterDTO $dto): SimpleResponse;

    public function getGoogleRedirectUrl(): string;

    public function handleGoogleCallback(): string;

    public function completeGoogleRegistration(?User $user, int $departmentId, string $password): SimpleResponse;

    public function logout(User $user, bool $logoutFromAllDevices = false): SimpleResponse;
}
