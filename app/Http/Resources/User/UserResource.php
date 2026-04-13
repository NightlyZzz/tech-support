<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'email' => $this->resource->email,
            'first_name' => $this->resource->first_name,
            'last_name' => $this->resource->last_name,
            'middle_name' => $this->resource->middle_name,
            'full_name' => $this->resource->getFullName(),
            'secondary_email' => $this->resource->secondary_email,
            'role_id' => $this->resource->role?->id,
            'role_name' => $this->resource->role?->name,
            'department_id' => $this->resource->department?->id,
            'department_name' => $this->resource->department?->name,
            'requires_google_registration_completion' => $this->resource->requiresGoogleRegistrationCompletion(),
        ];
    }
}
