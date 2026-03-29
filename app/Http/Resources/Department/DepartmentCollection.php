<?php

namespace App\Http\Resources\Department;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class DepartmentCollection extends ResourceCollection
{
    public function toArray(Request $request): array
    {
        return [
            'data' => DepartmentResource::collection($this->collection)
        ];
    }
}
