<?php

namespace App\Services\Auth\Web;

use App\Enums\Role\RoleType;
use App\Events\User\UserLoggedOutEverywhere;
use App\Models\User;
use App\Services\DTO\Auth\Web\LoginDTO;
use App\Services\DTO\Auth\Web\RegisterDTO;
use App\Services\DTO\Response\SimpleResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Throwable;

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

    public function getGoogleRedirectUrl(): string
    {
        return Socialite::driver('google')
            ->stateless()
            ->redirect()
            ->getTargetUrl();
    }

    public function handleGoogleCallback(): string
    {
        try {
            $socialiteUser = Socialite::driver('google')
                ->stateless()
                ->user();

            $user = $this->resolveGoogleUser($socialiteUser);

            Auth::login($user, true);

            if ($user->requiresGoogleRegistrationCompletion()) {
                return $this->buildFrontendUrl('/auth/google/complete');
            }

            return $this->buildFrontendUrl('/profile');
        } catch (Throwable) {
            return $this->buildFrontendUrl('/auth', [
                'provider' => 'google',
                'status' => 'error',
            ]);
        }
    }

    public function completeGoogleRegistration(?User $user, int $departmentId, string $password): SimpleResponse
    {
        if (!$user instanceof User) {
            return new SimpleResponse(false, [
                'message' => 'Пользователь не авторизован',
            ]);
        }

        if (!$user->requiresGoogleRegistrationCompletion()) {
            return new SimpleResponse(false, [
                'message' => 'Завершение регистрации не требуется',
            ]);
        }

        $user->forceFill([
            'department_id' => $departmentId,
            'password' => $password,
        ])->save();

        return new SimpleResponse(true, [
            'message' => 'Регистрация через Google успешно завершена',
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

    private function resolveGoogleUser(SocialiteUser $socialiteUser): User
    {
        $googleId = (string)$socialiteUser->getId();
        $email = mb_strtolower(trim((string)$socialiteUser->getEmail()));
        $firstName = $this->resolveFirstName($socialiteUser);
        $lastName = $this->resolveLastName($socialiteUser);
        $avatar = $this->resolveAvatar($socialiteUser);

        $user = User::query()
            ->where('google_id', $googleId)
            ->orWhere('email', $email)
            ->first();

        if (!$user instanceof User) {
            $user = User::query()->create([
                'email' => $email,
                'password' => Hash::make(Str::random(32)),
                'first_name' => $firstName,
                'last_name' => $lastName,
                'middle_name' => '',
                'role_id' => RoleType::User->value,
                'department_id' => null,
                'google_id' => $googleId,
                'google_avatar' => $avatar,
            ]);
        } else {
            $user->forceFill([
                'google_id' => $googleId,
                'google_avatar' => $avatar,
                'first_name' => $user->first_name !== '' ? $user->first_name : $firstName,
                'last_name' => $user->last_name !== '' ? $user->last_name : $lastName,
            ])->save();
        }

        if ($user->email_verified_at === null) {
            $user->markEmailAsVerified();
        }

        return $user->refresh();
    }

    private function resolveFirstName(SocialiteUser $socialiteUser): string
    {
        $firstName = trim((string)($socialiteUser->user['given_name'] ?? ''));

        if ($firstName !== '') {
            return $firstName;
        }

        $name = trim((string)$socialiteUser->getName());

        if ($name === '') {
            return 'Google';
        }

        $parts = preg_split('/\s+/u', $name) ?: [];

        return trim((string)($parts[0] ?? 'Google'));
    }

    private function resolveLastName(SocialiteUser $socialiteUser): string
    {
        $lastName = trim((string)($socialiteUser->user['family_name'] ?? ''));

        if ($lastName !== '') {
            return $lastName;
        }

        $name = trim((string)$socialiteUser->getName());

        if ($name === '') {
            return '';
        }

        $parts = preg_split('/\s+/u', $name) ?: [];

        if (count($parts) <= 1) {
            return '';
        }

        array_shift($parts);

        return trim(implode(' ', $parts));
    }

    private function resolveAvatar(SocialiteUser $socialiteUser): ?string
    {
        $avatar = $socialiteUser->getAvatar();

        return is_string($avatar) && $avatar !== '' ? $avatar : null;
    }

    private function buildFrontendUrl(string $path, array $query = []): string
    {
        $frontendUrl = rtrim((string)config('app.frontend_url', env('FRONTEND_URL', 'http://localhost')), '/');
        $normalizedPath = '/' . ltrim($path, '/');
        $queryString = $query !== [] ? ('?' . http_build_query($query)) : '';

        return $frontendUrl . $normalizedPath . $queryString;
    }
}
