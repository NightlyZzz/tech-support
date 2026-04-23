<?php

namespace App\Services\DTO\Auth\App;

use App\Models\User;

readonly class AppAuthResponse
{
    public function __construct(
        private bool $success,
        private string $message,
        private ?string $accessToken = null,
        private ?User $user = null
    ) {
    }

    public function succeeded(): bool
    {
        return $this->success;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }
}
