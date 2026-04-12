<?php

namespace App\Services\User;

use App\Models\User;
use App\Services\DTO\Response\SimpleResponse;
use App\Services\DTO\User\AdminUpdateUserDTO;
use App\Services\DTO\User\UpdateUserDTO;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;

final class UserService implements UserServiceInterface
{
    private const int PER_PAGE = 15;

    public function showAll(User $user, ?string $searchQuery = null): LengthAwarePaginator
    {
        $query = User::query()->with(['role', 'department']);

        if ($searchQuery !== null) {
            $query->where(function (Builder $builder) use ($searchQuery): void {
                $likeSearchQuery = '%' . $searchQuery . '%';

                $builder
                    ->where('last_name', 'like', $likeSearchQuery)
                    ->orWhere('first_name', 'like', $likeSearchQuery)
                    ->orWhere('middle_name', 'like', $likeSearchQuery)
                    ->orWhere('email', 'like', $likeSearchQuery)
                    ->orWhere('secondary_email', 'like', $likeSearchQuery)
                    ->orWhereRaw(
                        "TRIM(CONCAT(last_name, ' ', first_name, ' ', middle_name)) like ?",
                        [$likeSearchQuery]
                    )
                    ->orWhereRaw(
                        "TRIM(CONCAT(first_name, ' ', last_name, ' ', middle_name)) like ?",
                        [$likeSearchQuery]
                    )
                    ->orWhereRaw(
                        "TRIM(CONCAT(last_name, ' ', first_name)) like ?",
                        [$likeSearchQuery]
                    )
                    ->orWhereRaw(
                        "TRIM(CONCAT(first_name, ' ', last_name)) like ?",
                        [$likeSearchQuery]
                    );
            });
        }

        return $query
            ->orderByDesc('role_id')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->orderBy('middle_name')
            ->paginate(self::PER_PAGE)
            ->withQueryString();
    }

    public function update(UpdateUserDTO|AdminUpdateUserDTO $dto): SimpleResponse
    {
        $data = [];

        if (($email = $dto->getEmail()) !== null) {
            $data['email'] = $email;
        }

        if (($firstName = $dto->getFirstName()) !== null) {
            $data['first_name'] = $firstName;
        }

        if (($lastName = $dto->getLastName()) !== null) {
            $data['last_name'] = $lastName;
        }

        if (($middleName = $dto->getMiddleName()) !== null) {
            $data['middle_name'] = $middleName;
        }

        if (($secondaryEmail = $dto->getSecondaryEmail()) !== null) {
            $data['secondary_email'] = $secondaryEmail;
        }

        if (($password = $dto->getNewPassword()) !== null) {
            $data['password'] = Hash::make($password);
        }

        if (($departmentId = $dto->getDepartmentId()) !== null) {
            $data['department_id'] = $departmentId;
        }

        if ($dto instanceof AdminUpdateUserDTO && ($roleId = $dto->getRoleId()) !== null) {
            $data['role_id'] = $roleId;
        }

        if ($data === []) {
            return new SimpleResponse(true, [
                'message' => 'Нет данных для обновления',
            ]);
        }

        $dto->getUser()->update($data);

        return new SimpleResponse(true, [
            'message' => 'Данные пользователя успешно обновлены',
        ]);
    }

    public function delete(User $user): SimpleResponse
    {
        $user->delete();

        return new SimpleResponse(true, [
            'message' => 'Пользователь был успешно удален',
        ]);
    }
}
