<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\Auth\AuthServiceInterface;
use App\Services\DTO\Auth\LoginDTO;
use App\Services\DTO\Auth\RegisterDTO;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Attributes\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function login(LoginRequest $request, AuthServiceInterface $service): JsonResponse
    {
        $response = $service->login(new LoginDTO($request));

        if ($response->succeeded() && $request->hasSession()) {
            $request->session()->regenerate();
        }

        return $this->respond(
            $response->getData(),
            $response->succeeded() ? Response::HTTP_OK : Response::HTTP_UNAUTHORIZED
        );
    }

    public function register(RegisterRequest $request, AuthServiceInterface $service): JsonResponse
    {
        $response = $service->register(new RegisterDTO($request));

        if ($response->succeeded() && $request->hasSession()) {
            $request->session()->regenerate();
        }

        return $this->respond(
            $response->getData(),
            $response->succeeded() ? Response::HTTP_CREATED : Response::HTTP_UNPROCESSABLE_ENTITY
        );
    }

    #[Middleware('auth:sanctum')]
    public function logout(Request $request, AuthServiceInterface $service): JsonResponse
    {
        $validated = $request->validate([
            'all_devices' => ['nullable', 'boolean'],
        ]);

        $response = $service->logout(
            $request->user(),
            (bool)($validated['all_devices'] ?? false)
        );

        Auth::guard('web')->logout();

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return $this->respond($response->getData());
    }

    public function fallback(): JsonResponse
    {
        return $this->respond([
            'message' => 'Unauthorized',
        ], Response::HTTP_UNAUTHORIZED);
    }
}
