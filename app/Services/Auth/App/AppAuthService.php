<?php

namespace App\Services\Auth\App;

use App\Enums\Role\RoleType;
use App\Events\User\UserLoggedOutEverywhere;
use App\Models\User;
use App\Services\DTO\Auth\App\AppAuthResponse;
use App\Services\DTO\Auth\App\AppLoginDTO;
use App\Services\DTO\Auth\App\AppRegisterDTO;
use App\Services\DTO\Response\SimpleResponse;
use Illuminate\Support\Facades\Hash;

final class AppAuthService implements AppAuthServiceInterface
{
    public function login(AppLoginDTO $dto): AppAuthResponse
    {
        $user = User::query()
            ->with(['role', 'department'])
            ->where('email', $dto->getEmail())
            ->first();

        if (!$user instanceof User || !Hash::check($dto->getPassword(), $user->password)) {
            return new AppAuthResponse(
                false,
                'Неверный логин или пароль'
            );
        }

        $accessToken = $user->createToken($dto->getDeviceName(), ['*'])->plainTextToken;

        return new AppAuthResponse(
            true,
            'Успешный вход',
            $accessToken,
            $user
        );
    }

    public function register(AppRegisterDTO $dto): AppAuthResponse
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
        $user->load(['role', 'department']);

        $accessToken = $user->createToken($dto->getDeviceName(), ['*'])->plainTextToken;

        return new AppAuthResponse(
            true,
            'Пользователь успешно зарегистрирован',
            $accessToken,
            $user
        );
    }

    public function logoutCurrentDevice(User $user): SimpleResponse
    {
        $currentToken = $user->currentAccessToken();

        if ($currentToken === null) {
            return new SimpleResponse(false, [
                'message' => 'Текущий access token не найден',
            ]);
        }

        $currentToken->delete();

        return new SimpleResponse(true, [
            'message' => 'Успешно произведен выход на текущем устройстве',
        ]);
    }

    public function logoutAllDevices(User $user): SimpleResponse
    {
        $user->tokens()->delete();

        broadcast(new UserLoggedOutEverywhere($user->id))->toOthers();

        return new SimpleResponse(true, [
            'message' => 'Успешно произведен выход со всех устройств',
        ]);
    }
}
