<?php

namespace Tests\Feature\Auth;

use App\Enums\Role\RoleType;
use App\Models\Department\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_current_user_endpoint(): void
    {
        $response = $this->getJson('/api/user');

        $response->assertUnauthorized();
    }

    public function test_user_can_register_and_start_authenticated_session(): void
    {
        $departmentId = Department::query()->value('id');

        $response = $this->postJson('/api/auth/register', [
            'email' => 'new.user@gmail.com',
            'password' => 'password123',
            'first_name' => 'Ivan',
            'last_name' => 'Ivanov',
            'middle_name' => 'Ivanovich',
            'department_id' => $departmentId,
            'remember' => true,
        ]);

        $response
            ->assertCreated()
            ->assertJson([
                'message' => 'Пользователь успешно зарегистрирован',
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'new.user@gmail.com',
            'first_name' => 'Ivan',
            'last_name' => 'Ivanov',
            'middle_name' => 'Ivanovich',
            'department_id' => $departmentId,
            'role_id' => RoleType::User->value,
        ]);

        $this->assertAuthenticated();
    }

    public function test_user_can_login_with_valid_credentials_and_start_authenticated_session(): void
    {
        $user = User::factory()->create([
            'email' => 'login.user@gmail.com',
            'password' => 'password123',
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password123',
            'remember' => false,
        ]);

        $response
            ->assertOk()
            ->assertJson([
                'message' => 'Успешный вход',
            ]);

        $profileResponse = $this->getJson('/api/user');

        $profileResponse
            ->assertOk()
            ->assertJson([
                'data' => [
                    'id' => $user->id,
                    'email' => 'login.user@gmail.com',
                ],
            ]);
    }

    public function test_login_fails_with_invalid_password(): void
    {
        $user = User::factory()->create([
            'email' => 'wrong.password@gmail.com',
            'password' => 'password123',
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'wrong-password123',
            'remember' => false,
        ]);

        $response
            ->assertUnauthorized()
            ->assertJson([
                'message' => 'Неверный логин или пароль',
            ]);
    }

    public function test_authenticated_user_can_fetch_own_profile(): void
    {
        $user = User::factory()->create([
            'email' => 'profile.user@gmail.com',
        ]);

        $response = $this
            ->actingAs($user)
            ->getJson('/api/user');

        $response
            ->assertOk()
            ->assertJson([
                'data' => [
                    'id' => $user->id,
                    'email' => 'profile.user@gmail.com',
                ],
            ]);
    }

    public function test_authenticated_user_can_logout_from_current_device(): void
    {
        $user = User::factory()->create([
            'email' => 'logout.user@gmail.com',
            'password' => 'password123',
        ]);

        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password123',
            'remember' => false,
        ]);

        $loginResponse
            ->assertOk()
            ->assertJson([
                'message' => 'Успешный вход',
            ]);

        $logoutResponse = $this->postJson('/api/auth/logout', [
            'all_devices' => false,
        ]);

        $logoutResponse
            ->assertOk()
            ->assertJson([
                'message' => 'Успешно произведен выход на текущем устройстве',
            ]);

        $this->assertGuest('web');
    }

    public function test_login_is_rate_limited_after_too_many_attempts(): void
    {
        User::factory()->create([
            'email' => 'rate.limit@gmail.com',
            'password' => 'password123',
        ]);

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $response = $this->postJson('/api/auth/login', [
                'email' => 'rate.limit@gmail.com',
                'password' => 'wrong-password',
                'remember' => false,
            ]);

            $response->assertUnauthorized();
        }

        $limitedResponse = $this->postJson('/api/auth/login', [
            'email' => 'rate.limit@gmail.com',
            'password' => 'wrong-password',
            'remember' => false,
        ]);

        $limitedResponse
            ->assertStatus(429)
            ->assertJson([
                'message' => 'Слишком много попыток входа. Попробуйте позже.',
            ]);
    }
}
