<?php

namespace App\Http\Controllers\Role;

use App\Http\Controllers\Controller;
use App\Http\Resources\Role\RoleCollection;
use App\Models\Role\Role;

class RoleController extends Controller
{
    public function showAll(): RoleCollection
    {
        return new RoleCollection(Role::all());
    }
}
