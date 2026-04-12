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

final class AuthService implements AuthServiceInterface
{
    public function login(LoginDTO $dto): SimpleResponse
    {
        $credentials = [
            'email' => $dto->getEmail(),
            'password' => $dto->getPassword(),
        ];

        if (!Auth::attempt($credentials, $dto->isRemember())) {
            return new SimpleResponse(false, [
                'message' => 'Неверный логин или пароль',
            ]);
        }

        $user = Auth::user();

        if (!$user instanceof User) {
            return new SimpleResponse(false, [
                'message' => 'Не удалось выполнить вход',
            ]);
        }

        return new SimpleResponse(true, [
            'message' => 'Успешный вход',
        ]);
    }

    public function register(RegisterDTO $dto): SimpleResponse
    {
        $user = User::query()->create([
            'email' => $dto->getEmail(),
            'password' => Hash::make($dto->getPassword()),
            'first_name' => $dto->getFirstName(),
            'last_name' => $dto->getLastName(),
            'middle_name' => $dto->getMiddleName(),
            'role_id' => RoleType::User->value,
            'department_id' => $dto->getDepartmentId(),
        ]);

        $user->markEmailAsVerified();

        Auth::login($user, $dto->isRemember());

        return new SimpleResponse(true, [
            'message' => 'Пользователь успешно зарегистрирован',
        ]);
    }

    public function logout(User $user, bool $logoutFromAllDevices = false): SimpleResponse
    {
        if ($logoutFromAllDevices) {
            $user->tokens()->delete();

            broadcast(new UserLoggedOutEverywhere($user->id))->toOthers();

            return new SimpleResponse(true, [
                'message' => 'Успешно произведен выход со всех устройств',
            ]);
        }

        return new SimpleResponse(true, [
            'message' => 'Успешно произведен выход на текущем устройстве',
        ]);
    }
}
