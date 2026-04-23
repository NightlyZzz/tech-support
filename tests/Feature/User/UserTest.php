<?php

namespace Tests\Feature\User;

use App\Enums\Role\RoleType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_user_can_view_own_profile(): void
    {
        $user = User::factory()->withRole(RoleType::User)->create([
            'email' => 'self.profile@gmail.com',
        ]);

        $response = $this
            ->withAuthToken($user)
            ->getJson('/api/user');

        $response
            ->assertOk()
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.email', 'self.profile@gmail.com');
    }

    public function test_default_user_cannot_view_users_list(): void
    {
        $user = User::factory()->withRole(RoleType::User)->create();

        $response = $this
            ->withAuthToken($user)
            ->getJson('/api/user/all');

        $response->assertForbidden();
    }

    public function test_admin_can_view_users_list(): void
    {
        $admin = User::factory()->withRole(RoleType::Admin)->create();
        User::factory()->withRole(RoleType::User)->create([
            'email' => 'first.user@gmail.com',
        ]);
        User::factory()->withRole(RoleType::Employee)->create([
            'email' => 'second.user@gmail.com',
        ]);

        $response = $this
            ->withAuthToken($admin)
            ->getJson('/api/user/all');

        $response
            ->assertOk()
            ->assertJsonFragment([
                'email' => 'first.user@gmail.com',
            ])
            ->assertJsonFragment([
                'email' => 'second.user@gmail.com',
            ]);
    }

    public function test_admin_can_search_users(): void
    {
        $admin = User::factory()->withRole(RoleType::Admin)->create();
        User::factory()->withRole(RoleType::User)->create([
            'first_name' => 'Target',
            'last_name' => 'Person',
            'middle_name' => 'User',
            'email' => 'target.person@gmail.com',
        ]);
        User::factory()->withRole(RoleType::User)->create([
            'first_name' => 'Another',
            'last_name' => 'Employee',
            'middle_name' => 'User',
            'email' => 'another.person@gmail.com',
        ]);

        $response = $this
            ->withAuthToken($admin)
            ->getJson('/api/user/all?search=Target');

        $response
            ->assertOk()
            ->assertJsonFragment([
                'email' => 'target.person@gmail.com',
            ])
            ->assertJsonMissing([
                'email' => 'another.person@gmail.com',
            ]);
    }

    public function test_admin_can_view_user_by_id(): void
    {
        $admin = User::factory()->withRole(RoleType::Admin)->create();
        $targetUser = User::factory()->withRole(RoleType::User)->create([
            'email' => 'target.user@gmail.com',
        ]);

        $response = $this
            ->withAuthToken($admin)
            ->getJson('/api/user/' . $targetUser->id);

        $response
            ->assertOk()
            ->assertJsonPath('data.id', $targetUser->id)
            ->assertJsonPath('data.email', 'target.user@gmail.com');
    }

    public function test_default_user_cannot_view_another_user_by_id(): void
    {
        $user = User::factory()->withRole(RoleType::User)->create();
        $anotherUser = User::factory()->withRole(RoleType::User)->create();

        $response = $this
            ->withAuthToken($user)
            ->getJson('/api/user/' . $anotherUser->id);

        $response->assertForbidden();
    }

    public function test_default_user_can_update_own_profile(): void
    {
        $user = User::factory()->withRole(RoleType::User)->create([
            'first_name' => 'Old',
            'last_name' => 'Name',
            'middle_name' => 'Value',
            'email' => 'old.profile@gmail.com',
        ]);

        $response = $this
            ->withAuthToken($user)
            ->putJson('/api/user', [
                'first_name' => 'New',
                'last_name' => 'Profile',
                'middle_name' => 'Updated',
                'email' => 'new.profile@gmail.com',
            ]);

        $response
            ->assertOk()
            ->assertJson([
                'message' => 'Данные пользователя успешно обновлены',
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'first_name' => 'New',
            'last_name' => 'Profile',
            'middle_name' => 'Updated',
            'email' => 'new.profile@gmail.com',
        ]);
    }

    public function test_admin_can_update_another_user(): void
    {
        $admin = User::factory()->withRole(RoleType::Admin)->create();
        $targetUser = User::factory()->withRole(RoleType::User)->create([
            'first_name' => 'Before',
            'email' => 'before.update@gmail.com',
            'role_id' => RoleType::User->value,
        ]);

        $response = $this
            ->withAuthToken($admin)
            ->putJson('/api/user/' . $targetUser->id, [
                'first_name' => 'After',
                'email' => 'after.update@gmail.com',
                'role_id' => RoleType::Employee->value,
            ]);

        $response
            ->assertOk()
            ->assertJson([
                'message' => 'Данные пользователя успешно обновлены',
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $targetUser->id,
            'first_name' => 'After',
            'email' => 'after.update@gmail.com',
            'role_id' => RoleType::Employee->value,
        ]);
    }

    public function test_default_user_cannot_update_another_user(): void
    {
        $user = User::factory()->withRole(RoleType::User)->create();
        $anotherUser = User::factory()->withRole(RoleType::User)->create([
            'first_name' => 'Protected',
        ]);

        $response = $this
            ->withAuthToken($user)
            ->putJson('/api/user/' . $anotherUser->id, [
                'first_name' => 'Hacked',
            ]);

        $response->assertForbidden();

        $this->assertDatabaseHas('users', [
            'id' => $anotherUser->id,
            'first_name' => 'Protected',
        ]);
    }

    public function test_admin_can_delete_another_user(): void
    {
        $admin = User::factory()->withRole(RoleType::Admin)->create();
        $targetUser = User::factory()->withRole(RoleType::User)->create();

        $response = $this
            ->withAuthToken($admin)
            ->deleteJson('/api/user/' . $targetUser->id);

        $response
            ->assertOk()
            ->assertJson([
                'message' => 'Пользователь был успешно удален',
            ]);

        $this->assertDatabaseMissing('users', [
            'id' => $targetUser->id,
        ]);
    }

    public function test_default_user_cannot_delete_another_user(): void
    {
        $user = User::factory()->withRole(RoleType::User)->create();
        $anotherUser = User::factory()->withRole(RoleType::User)->create();

        $response = $this
            ->withAuthToken($user)
            ->deleteJson('/api/user/' . $anotherUser->id);

        $response->assertForbidden();

        $this->assertDatabaseHas('users', [
            'id' => $anotherUser->id,
        ]);
    }

    public function test_default_user_can_delete_own_account(): void
    {
        $user = User::factory()->withRole(RoleType::User)->create();

        $response = $this
            ->withAuthToken($user)
            ->deleteJson('/api/user');

        $response
            ->assertOk()
            ->assertJson([
                'message' => 'Пользователь был успешно удален',
            ]);

        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
    }
}
