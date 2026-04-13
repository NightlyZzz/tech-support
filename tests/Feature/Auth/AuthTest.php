<?php

namespace Tests\Feature\Auth;

use App\Enums\Role\RoleType;
use App\Models\Department\Department;
use App\Models\User;
use App\Services\Auth\AuthServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
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
                    'requires_google_registration_completion' => false,
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
                    'requires_google_registration_completion' => false,
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

    public function test_google_redirect_endpoint_redirects_to_provider_url(): void
    {
        $this->mock(AuthServiceInterface::class, function (MockInterface $mock): void {
            $mock
                ->shouldReceive('getGoogleRedirectUrl')
                ->once()
                ->andReturn('https://accounts.google.com/o/oauth2/auth?mock=1');
        });

        $response = $this->get('/auth/google/redirect');

        $response
            ->assertRedirect('https://accounts.google.com/o/oauth2/auth?mock=1');
    }

    public function test_google_callback_redirects_to_profile_url_when_service_returns_success_url(): void
    {
        $this->mock(AuthServiceInterface::class, function (MockInterface $mock): void {
            $mock
                ->shouldReceive('handleGoogleCallback')
                ->once()
                ->andReturn('http://127.0.0.1/profile');
        });

        $response = $this->get('/auth/google/callback');

        $response
            ->assertRedirect('http://127.0.0.1/profile');
    }

    public function test_google_callback_redirects_to_completion_url_when_service_requires_profile_completion(): void
    {
        $this->mock(AuthServiceInterface::class, function (MockInterface $mock): void {
            $mock
                ->shouldReceive('handleGoogleCallback')
                ->once()
                ->andReturn('http://127.0.0.1/auth/google/complete');
        });

        $response = $this->get('/auth/google/callback');

        $response
            ->assertRedirect('http://127.0.0.1/auth/google/complete');
    }

    public function test_google_callback_redirects_to_auth_with_error_query_when_service_returns_error_url(): void
    {
        $this->mock(AuthServiceInterface::class, function (MockInterface $mock): void {
            $mock
                ->shouldReceive('handleGoogleCallback')
                ->once()
                ->andReturn('http://127.0.0.1/auth?provider=google&status=error');
        });

        $response = $this->get('/auth/google/callback');

        $response
            ->assertRedirect('http://127.0.0.1/auth?provider=google&status=error');
    }

    public function test_authenticated_google_user_can_complete_google_registration(): void
    {
        $departmentId = Department::query()->value('id');

        $user = User::factory()->withoutDepartment()->create([
            'email' => 'google.user@gmail.com',
            'google_id' => 'google-id-1',
            'google_avatar' => 'https://example.com/avatar.jpg',
        ]);

        $response = $this
            ->actingAs($user)
            ->postJson('/api/auth/google/complete-registration', [
                'department_id' => $departmentId,
                'password' => 'new-password123',
            ]);

        $response
            ->assertOk()
            ->assertJson([
                'message' => 'Регистрация через Google успешно завершена',
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'department_id' => $departmentId,
            'google_id' => 'google-id-1',
        ]);

        $profileResponse = $this
            ->actingAs($user->fresh())
            ->getJson('/api/user');

        $profileResponse
            ->assertOk()
            ->assertJson([
                'data' => [
                    'id' => $user->id,
                    'requires_google_registration_completion' => false,
                    'department_id' => $departmentId,
                ],
            ]);
    }

    public function test_google_registration_completion_requires_authenticated_user(): void
    {
        $departmentId = Department::query()->value('id');

        $response = $this->postJson('/api/auth/google/complete-registration', [
            'department_id' => $departmentId,
            'password' => 'new-password123',
        ]);

        $response->assertUnauthorized();
    }

    public function test_google_registration_completion_fails_for_user_that_does_not_require_completion(): void
    {
        $departmentId = Department::query()->value('id');

        $user = User::factory()->create([
            'email' => 'completed.google.user@gmail.com',
            'google_id' => 'google-id-2',
            'department_id' => $departmentId,
        ]);

        $response = $this
            ->actingAs($user)
            ->postJson('/api/auth/google/complete-registration', [
                'department_id' => $departmentId,
                'password' => 'new-password123',
            ]);

        $response
            ->assertUnprocessable()
            ->assertJson([
                'message' => 'Завершение регистрации не требуется',
            ]);
    }

    public function test_google_registration_completion_validates_required_fields(): void
    {
        $user = User::factory()->withoutDepartment()->create([
            'email' => 'validation.google.user@gmail.com',
            'google_id' => 'google-id-3',
        ]);

        $response = $this
            ->actingAs($user)
            ->postJson('/api/auth/google/complete-registration', [
                'department_id' => null,
                'password' => '123',
            ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'department_id',
                'password',
            ]);
    }
}
