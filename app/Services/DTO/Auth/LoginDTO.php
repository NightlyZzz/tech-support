<?php

namespace App\Services\DTO\Auth;

use App\Http\Requests\Auth\LoginRequest;

readonly class LoginDTO
{
    private string $email;
    private string $password;
    private bool $remember;

    public function __construct(LoginRequest $request)
    {
        $this->email = $request->getEmail();
        $this->password = $request->getPassword();
        $this->remember = $request->getRemember();
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function isRemember(): bool
    {
        return $this->remember;
    }
}
