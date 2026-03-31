<?php

namespace App\Http\Controllers\Role;

use App\Http\Controllers\Controller;
use App\Http\Resources\Role\RoleCollection;
use App\Models\Role\Role;
use Illuminate\Support\Facades\Cache;

class RoleController extends Controller
{
    public function showAll(): RoleCollection
    {
        return new RoleCollection(
            Cache::remember('roles', 3600, fn () => Role::all())
        );
    }
}
