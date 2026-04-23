<?php

namespace App\Http\Controllers\Auth\App;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\App\AppLoginRequest;
use App\Http\Requests\Auth\App\AppRegisterRequest;
use App\Http\Resources\Auth\App\AppAuthResource;
use App\Models\User;
use App\Services\Auth\App\AppAuthServiceInterface;
use App\Services\DTO\Auth\App\AppLoginDTO;
use App\Services\DTO\Auth\App\AppRegisterDTO;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function login(AppLoginRequest $request, AppAuthServiceInterface $service): JsonResponse
    {
        $response = $service->login(new AppLoginDTO($request));

        return new AppAuthResource($response)
            ->response()
            ->setStatusCode($response->succeeded() ? Response::HTTP_OK : Response::HTTP_UNAUTHORIZED);
    }

    public function register(AppRegisterRequest $request, AppAuthServiceInterface $service): JsonResponse
    {
        $response = $service->register(new AppRegisterDTO($request));

        return new AppAuthResource($response)
            ->response()
            ->setStatusCode($response->succeeded() ? Response::HTTP_CREATED : Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function logout(Request $request, AppAuthServiceInterface $service): JsonResponse
    {
        $user = $request->user();

        if (!$user instanceof User) {
            return $this->respond([
                'message' => 'Пользователь не авторизован',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $response = $service->logoutCurrentDevice($user);

        return $this->respond(
            $response->getData(),
            $response->succeeded() ? Response::HTTP_OK : Response::HTTP_UNAUTHORIZED
        );
    }

    public function logoutAll(Request $request, AppAuthServiceInterface $service): JsonResponse
    {
        $user = $request->user();

        if (!$user instanceof User) {
            return $this->respond([
                'message' => 'Пользователь не авторизован',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $response = $service->logoutAllDevices($user);

        return $this->respond(
            $response->getData(),
            $response->succeeded() ? Response::HTTP_OK : Response::HTTP_UNAUTHORIZED
        );
    }
}
