<?php

namespace App\Http\Resources\User;

use App\Http\Resources\Concerns\HasPaginationMeta;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class UserCollection extends ResourceCollection
{
    use HasPaginationMeta;

    public function toArray(Request $request): array
    {
        return [
            'data' => UserResource::collection($this->collection),
        ];
    }
}
