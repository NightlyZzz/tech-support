<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\AdminUpdateUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\User\UserCollection;
use App\Http\Resources\User\UserResource;
use App\Models\User;
use App\Services\DTO\User\AdminUpdateUserDTO;
use App\Services\DTO\User\UpdateUserDTO;
use App\Services\User\UserServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Routing\Attributes\Controllers\Middleware;
use Illuminate\Routing\Attributes\Controllers\Authorize;

#[Middleware('auth:sanctum')]
class UserController extends Controller
{
    public function show(): UserResource
    {
        return new UserResource(Auth::user());
    }

    #[Authorize('viewAny', User::class)]
    public function showById(User $user): UserResource
    {
        return new UserResource($user);
    }

    #[Authorize('viewAny', User::class)]
    public function showAll(): UserCollection
    {
        return new UserCollection(User::with(['role', 'department'])->get());
    }

    public function update(UpdateUserRequest $request, UserServiceInterface $service): JsonResponse
    {
        $response = $service->update(new UpdateUserDTO($request));
        return $this->respond(
            $response->getData(),
            $response->succeeded() ? Response::HTTP_OK : Response::HTTP_FORBIDDEN
        );
    }

    #[Authorize('updateAny', User::class)]
    public function updateById(User $user, AdminUpdateUserRequest $request, UserServiceInterface $service): JsonResponse
    {
        $response = $service->update(new AdminUpdateUserDTO($user, $request));
        return $this->respond(
            $response->getData(),
            $response->succeeded() ? Response::HTTP_OK : Response::HTTP_FORBIDDEN
        );
    }

    public function destroy(UserServiceInterface $service): JsonResponse
    {
        $response = $service->delete(Auth::user());
        return $this->respond(
            $response->getData(),
            $response->succeeded() ? Response::HTTP_OK : Response::HTTP_FORBIDDEN
        );
    }

    #[Authorize('deleteAny', User::class)]
    public function destroyById(User $user, UserServiceInterface $service): JsonResponse
    {
        $response = $service->delete($user);
        return $this->respond(
            $response->getData(),
            $response->succeeded() ? Response::HTTP_OK : Response::HTTP_FORBIDDEN
        );
    }
}
