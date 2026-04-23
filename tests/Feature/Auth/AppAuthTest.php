<?php

namespace Tests\Feature\Auth;

use App\Enums\Role\RoleType;
use App\Models\Department\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

class AppAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_app_current_user_endpoint(): void
    {
        $response = $this->getJson('/api/app/user');

        $response->assertUnauthorized();
    }

    public function test_user_can_register_in_app_and_receive_access_token(): void
    {
        $departmentId = Department::query()->value('id');

        $response = $this->postJson('/api/app/auth/register', [
            'email' => 'mobile.user@gmail.com',
            'password' => 'password123',
            'first_name' => 'Mobile',
            'last_name' => 'User',
            'middle_name' => 'Client',
            'department_id' => $departmentId,
            'device_name' => 'iPhone 15 Pro',
        ]);

        $response
            ->assertCreated()
            ->assertJson([
                'message' => 'Пользователь успешно зарегистрирован',
                'token_type' => 'Bearer',
            ])
            ->assertJsonPath('user.email', 'mobile.user@gmail.com')
            ->assertJsonPath('user.department_id', $departmentId)
            ->assertJsonPath('user.role_id', RoleType::User->value);

        $this->assertDatabaseHas('users', [
            'email' => 'mobile.user@gmail.com',
            'role_id' => RoleType::User->value,
            'department_id' => $departmentId,
        ]);

        $this->assertNotEmpty($response->json('access_token'));
    }

    public function test_user_can_login_in_app_and_receive_access_token(): void
    {
        $user = User::factory()->withRole(RoleType::User)->create([
            'email' => 'app.login@gmail.com',
            'password' => 'password123',
        ]);

        $response = $this->postJson('/api/app/auth/login', [
            'email' => $user->email,
            'password' => 'password123',
            'device_name' => 'MacBook Pro',
        ]);

        $response
            ->assertOk()
            ->assertJson([
                'message' => 'Успешный вход',
                'token_type' => 'Bearer',
            ])
            ->assertJsonPath('user.id', $user->id)
            ->assertJsonPath('user.email', 'app.login@gmail.com');

        $this->assertNotEmpty($response->json('access_token'));
    }

    public function test_app_login_fails_with_invalid_password(): void
    {
        $user = User::factory()->create([
            'email' => 'invalid.app.login@gmail.com',
            'password' => 'password123',
        ]);

        $response = $this->postJson('/api/app/auth/login', [
            'email' => $user->email,
            'password' => 'wrong-password123',
            'device_name' => 'iPad',
        ]);

        $response
            ->assertUnauthorized()
            ->assertJson([
                'message' => 'Неверный логин или пароль',
            ]);
    }

    public function test_authenticated_app_user_can_fetch_own_profile(): void
    {
        $user = User::factory()->withRole(RoleType::User)->create([
            'email' => 'app.profile@gmail.com',
        ]);

        $response = $this
            ->withAuthToken($user, 'iphone_test_token')
            ->getJson('/api/app/user');

        $response
            ->assertOk()
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.email', 'app.profile@gmail.com');
    }

    public function test_authenticated_app_user_can_logout_from_current_device(): void
    {
        $user = User::factory()->create([
            'email' => 'app.logout@gmail.com',
            'password' => 'password123',
        ]);

        $loginResponse = $this->postJson('/api/app/auth/login', [
            'email' => $user->email,
            'password' => 'password123',
            'device_name' => 'iPhone',
        ]);

        $plainTextToken = $loginResponse->json('access_token');
        $tokenId = (int)explode('|', (string)$plainTextToken)[0];

        $this->withHeader('Authorization', 'Bearer ' . $plainTextToken)
            ->postJson('/api/app/auth/logout')
            ->assertOk()
            ->assertJson([
                'message' => 'Успешно произведен выход на текущем устройстве',
            ]);

        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $tokenId,
        ]);
    }

    public function test_authenticated_app_user_can_logout_from_all_devices(): void
    {
        $user = User::factory()->create();

        $firstToken = $user->createToken('iphone')->plainTextToken;
        $user->createToken('macbook')->plainTextToken;
        $user->createToken('ipad')->plainTextToken;

        $this->assertSame(3, PersonalAccessToken::query()->where('tokenable_id', $user->id)->count());

        $this->withHeader('Authorization', 'Bearer ' . $firstToken)
            ->postJson('/api/app/auth/logout-all')
            ->assertOk()
            ->assertJson([
                'message' => 'Успешно произведен выход со всех устройств',
            ]);

        $this->assertSame(0, PersonalAccessToken::query()->where('tokenable_id', $user->id)->count());
    }

    public function test_app_registration_is_rate_limited_after_too_many_attempts(): void
    {
        $departmentId = Department::query()->value('id');

        for ($attempt = 1; $attempt <= 3; $attempt++) {
            $response = $this->postJson('/api/app/auth/register', [
                'email' => 'rate.app.register@gmail.com',
                'password' => 'password123',
                'first_name' => 'Rate',
                'last_name' => 'Register',
                'middle_name' => 'Limit',
                'department_id' => $departmentId,
                'device_name' => 'iPhone',
            ]);

            if ($attempt === 1) {
                $response->assertCreated();
            } else {
                $response->assertUnprocessable();
            }
        }

        $limitedResponse = $this->postJson('/api/app/auth/register', [
            'email' => 'rate.app.register@gmail.com',
            'password' => 'password123',
            'first_name' => 'Rate',
            'last_name' => 'Register',
            'middle_name' => 'Limit',
            'department_id' => $departmentId,
            'device_name' => 'iPhone',
        ]);

        $limitedResponse
            ->assertStatus(429)
            ->assertJson([
                'message' => 'Слишком много попыток регистрации в приложении. Попробуйте позже.',
            ]);
    }
}
