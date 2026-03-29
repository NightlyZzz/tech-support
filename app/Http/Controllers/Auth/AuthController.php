<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\Auth\AuthServiceInterface;
use App\Services\DTO\Auth\LoginDTO;
use App\Services\DTO\Auth\RegisterDTO;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function login(LoginRequest $request, AuthServiceInterface $service): JsonResponse
    {
        $response = $service->login(new LoginDTO($request));
        return $this->respond(
            $response->getData(),
            $response->succeeded() ? Response::HTTP_OK : Response::HTTP_UNAUTHORIZED
        );
    }

    public function register(RegisterRequest $request, AuthServiceInterface $service): JsonResponse
    {
        $response = $service->register(new RegisterDTO($request));
        return $this->respond(
            $response->getData(),
            $response->succeeded() ? Response::HTTP_OK : Response::HTTP_UNAUTHORIZED
        );
    }

    public function logout(AuthServiceInterface $service): JsonResponse
    {
        /** @noinspection PhpParamsInspection */
        $response = $service->logout(Auth::user());
        return $this->respond($response->getData());
    }

    public function fallback(): JsonResponse
    {
        return $this->respond([
            'message' => 'Unauthorized'
        ], Response::HTTP_UNAUTHORIZED);
    }
}
