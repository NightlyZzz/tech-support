<?php

namespace App\Services\Auth\App;

use App\Models\User;
use App\Services\DTO\Auth\App\AppLoginDTO;
use App\Services\DTO\Auth\App\AppRegisterDTO;
use App\Services\DTO\Auth\App\AppAuthResponse;
use App\Services\DTO\Response\SimpleResponse;

interface AppAuthServiceInterface
{
    public function login(AppLoginDTO $dto): AppAuthResponse;

    public function register(AppRegisterDTO $dto): AppAuthResponse;

    public function logoutCurrentDevice(User $user): SimpleResponse;

    public function logoutAllDevices(User $user): SimpleResponse;
}
