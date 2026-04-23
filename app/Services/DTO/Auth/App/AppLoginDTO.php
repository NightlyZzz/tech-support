<?php

namespace App\Services\DTO\Auth\App;

use App\Http\Requests\Auth\App\AppLoginRequest;

readonly class AppLoginDTO
{
    private string $email;
    private string $password;
    private string $deviceName;

    public function __construct(AppLoginRequest $request)
    {
        $this->email = $request->getEmail();
        $this->password = $request->getPassword();
        $this->deviceName = $request->getDeviceName();
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getDeviceName(): string
    {
        return $this->deviceName;
    }
}
