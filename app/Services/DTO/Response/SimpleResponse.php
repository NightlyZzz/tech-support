<?php

namespace App\Services\DTO\Response;

readonly class SimpleResponse
{
    public function __construct(
        private bool $success,
        private array $data
    ) {}

    public function succeeded(): bool
    {
        return $this->success;
    }

    public function getData(): array
    {
        return $this->data;
    }
}
