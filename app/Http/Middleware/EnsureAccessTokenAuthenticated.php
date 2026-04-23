<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccessTokenAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $bearerToken = $request->bearerToken();

        if ($user === null || !is_string($bearerToken) || $bearerToken === '' || $user->currentAccessToken() === null) {
            return new JsonResponse([
                'message' => 'Unauthorized',
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
