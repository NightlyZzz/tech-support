<?php

namespace App\Http\Controllers\User\Web;

use App\Events\User\UserDeleted;
use App\Events\User\UserUpdated;
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
use Illuminate\Http\Request;
use Illuminate\Routing\Attributes\Controllers\Authorize;
use Illuminate\Routing\Attributes\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

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
    public function showAll(Request $request, UserServiceInterface $service): UserCollection
    {
        $request->validate([
            'search' => ['nullable', 'string', 'max:255']
        ]);

        $searchQuery = trim((string)$request->query('search', ''));
        $searchQuery = $searchQuery !== '' ? $searchQuery : null;

        return new UserCollection(
            $service->showAll(Auth::user(), $searchQuery)
        );
    }

    public function update(UpdateUserRequest $request, UserServiceInterface $service): JsonResponse
    {
        $response = $service->update(new UpdateUserDTO($request));

        $updatedUser = Auth::user()?->fresh(['role', 'department']);

        if ($updatedUser !== null) {
            broadcast(new UserUpdated($updatedUser))->toOthers();
        }

        return $this->respond(
            $response->getData(),
            $response->succeeded() ? Response::HTTP_OK : Response::HTTP_FORBIDDEN
        );
    }

    #[Authorize('updateAny', User::class)]
    public function updateById(User $user, AdminUpdateUserRequest $request, UserServiceInterface $service): JsonResponse
    {
        $response = $service->update(new AdminUpdateUserDTO($user, $request));

        $updatedUser = $user->fresh(['role', 'department']);

        if ($updatedUser !== null) {
            broadcast(new UserUpdated($updatedUser))->toOthers();
        }

        return $this->respond(
            $response->getData(),
            $response->succeeded() ? Response::HTTP_OK : Response::HTTP_FORBIDDEN
        );
    }

    public function destroy(UserServiceInterface $service): JsonResponse
    {
        $userId = (int)Auth::id();

        $response = $service->delete(Auth::user());

        if ($response->succeeded()) {
            broadcast(new UserDeleted($userId))->toOthers();
        }

        return $this->respond(
            $response->getData(),
            $response->succeeded() ? Response::HTTP_OK : Response::HTTP_FORBIDDEN
        );
    }

    #[Authorize('deleteAny', User::class)]
    public function destroyById(User $user, UserServiceInterface $service): JsonResponse
    {
        $userId = $user->id;

        $response = $service->delete($user);

        if ($response->succeeded()) {
            broadcast(new UserDeleted($userId))->toOthers();
        }

        return $this->respond(
            $response->getData(),
            $response->succeeded() ? Response::HTTP_OK : Response::HTTP_FORBIDDEN
        );
    }
}
