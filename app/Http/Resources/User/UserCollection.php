<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class UserCollection extends ResourceCollection
{
    public function toArray(Request $request): array
    {
        return [
            'data' => UserResource::collection($this->collection)
        ];
    }

    public function paginationInformation($request, $paginated, $default): array
    {
        $default['meta']['current_page'] = (int)$paginated['current_page'];
        $default['meta']['last_page'] = (int)$paginated['last_page'];
        $default['meta']['per_page'] = (int)$paginated['per_page'];
        $default['meta']['total'] = (int)$paginated['total'];

        return $default;
    }
}
