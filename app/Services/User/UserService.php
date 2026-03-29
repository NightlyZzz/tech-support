<?php

namespace App\Services\User;

use App\Models\User;
use App\Services\DTO\Response\SimpleResponse;
use App\Services\DTO\User\AdminUpdateUserDTO;
use App\Services\DTO\User\UpdateUserDTO;
use Illuminate\Support\Facades\Hash;

final class UserService implements UserServiceInterface
{
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

        $dto->getUser()->update($data);

        return new SimpleResponse(true, [
            'message' => 'Данные пользователя успешно обновлены'
        ]);
    }

    public function delete(User $user): SimpleResponse
    {
        $user->delete();
        return new SimpleResponse(true, [
            'message' => 'Пользователь был  успешно удален'
        ]);
    }
}
