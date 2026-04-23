<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function withAuthToken(User $user, string $tokenName = 'test_token'): static
    {
        $token = $user->createToken($tokenName, ['*'])->plainTextToken;

        return $this->withHeader('Authorization', 'Bearer ' . $token);
    }
}
