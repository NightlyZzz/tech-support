<?php

namespace App\Http\Controllers\Department;

use App\Http\Controllers\Controller;
use App\Http\Resources\Department\DepartmentCollection;
use App\Models\Department\Department;
use Illuminate\Support\Facades\Cache;

class DepartmentController extends Controller
{
    public function showAll(): DepartmentCollection
    {
        return new DepartmentCollection(
            Cache::remember('departments', 3600, fn () => Department::all())
        );
    }
}
