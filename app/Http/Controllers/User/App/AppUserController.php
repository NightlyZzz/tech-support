<?php

namespace App\Http\Controllers\User\App;

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
use Symfony\Component\HttpFoundation\Response;

class AppUserController extends Controller
{
    public function show(Request $request): UserResource
    {
        $user = $request->user();

        if (!$user instanceof User) {
            abort(Response::HTTP_UNAUTHORIZED);
        }

        return new UserResource($user->loadMissing(['role', 'department']));
    }

    #[Authorize('viewAny', User::class)]
    public function showById(User $user): UserResource
    {
        return new UserResource($user->loadMissing(['role', 'department']));
    }

    #[Authorize('viewAny', User::class)]
    public function showAll(Request $request, UserServiceInterface $service): UserCollection
    {
        $user = $request->user();

        if (!$user instanceof User) {
            abort(Response::HTTP_UNAUTHORIZED);
        }

        $searchQuery = $request->query('search');

        if (!is_string($searchQuery)) {
            $searchQuery = null;
        }

        $searchQuery = $searchQuery !== null ? trim($searchQuery) : null;
        $searchQuery = $searchQuery === '' ? null : $searchQuery;

        return new UserCollection($service->showAll($user, $searchQuery));
    }

    public function update(UpdateUserRequest $request, UserServiceInterface $service): JsonResponse
    {
        $response = $service->update(new UpdateUserDTO($request));

        $updatedUser = $request->user()?->fresh(['role', 'department']);

        if ($updatedUser instanceof User) {
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

        if ($updatedUser instanceof User) {
            broadcast(new UserUpdated($updatedUser))->toOthers();
        }

        return $this->respond(
            $response->getData(),
            $response->succeeded() ? Response::HTTP_OK : Response::HTTP_FORBIDDEN
        );
    }

    public function destroy(Request $request, UserServiceInterface $service): JsonResponse
    {
        $user = $request->user();

        if (!$user instanceof User) {
            abort(Response::HTTP_UNAUTHORIZED);
        }

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
