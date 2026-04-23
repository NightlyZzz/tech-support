<?php

namespace App\Http\Resources\Auth\App;

use App\Http\Resources\User\UserResource;
use App\Services\DTO\Auth\App\AppAuthResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppAuthResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        /** @var AppAuthResponse $response */
        $response = $this->resource;

        $data = [
            'message' => $response->getMessage(),
        ];

        if (!$response->succeeded()) {
            return $data;
        }

        $user = $response->getUser();

        return array_merge($data, [
            'token_type' => 'Bearer',
            'access_token' => $response->getAccessToken(),
            'user' => $user !== null
                ? new UserResource($user->loadMissing(['role', 'department']))->toArray($request)
                : null,
        ]);
    }
}
