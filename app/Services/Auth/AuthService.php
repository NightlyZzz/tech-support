<?php

namespace App\Services\Auth;

use App\Enums\Role\RoleType;
use App\Events\User\UserLoggedOutEverywhere;
use App\Models\User;
use App\Services\DTO\Auth\LoginDTO;
use App\Services\DTO\Auth\RegisterDTO;
use App\Services\DTO\Response\SimpleResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;

final class AuthService implements AuthServiceInterface
{
    public function login(LoginDTO|RegisterDTO $dto, ?User $user = null): SimpleResponse
    {
        $auth = Auth::attempt([
            'email' => $dto->getEmail(),
            'password' => $dto->getPassword()
        ], $dto->isRemember());

        if (!$auth) {
            return new SimpleResponse(false, [
                'message' => 'Неверный логин или пароль'
            ]);
        }

        /** @var User $user */
        $user ??= Auth::user();

        $token = $user->createToken('auth_token')->plainTextToken;

        return new SimpleResponse(true, [
            'token' => $token
        ]);
    }

    public function register(RegisterDTO $dto): SimpleResponse
    {
        $user = User::create([
            'email' => $dto->getEmail(),
            'password' => Hash::make($dto->getPassword()),
            'first_name' => $dto->getFirstName(),
            'last_name' => $dto->getLastName(),
            'middle_name' => $dto->getMiddleName(),
            'role_id' => RoleType::User->value,
            'department_id' => $dto->getDepartmentId()
        ]);

        $user->markEmailAsVerified();

        return $this->login($dto, $user);
    }

    public function logout(User $user, ?PersonalAccessToken $currentToken = null, bool $logoutFromAllDevices = false): SimpleResponse
    {
        if ($logoutFromAllDevices) {
            $user->tokens()->delete();

            broadcast(new UserLoggedOutEverywhere($user->id))->toOthers();

            return new SimpleResponse(true, [
                'message' => 'Успешно произведен выход со всех устройств'
            ]);
        }

        if ($currentToken !== null) {
            $currentToken->delete();
        }

        return new SimpleResponse(true, [
            'message' => 'Успешно произведен выход на текущем устройстве'
        ]);
    }
}
