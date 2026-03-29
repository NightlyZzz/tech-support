<?php

namespace App\Http\Controllers\Department;

use App\Http\Controllers\Controller;
use App\Http\Resources\Department\DepartmentCollection;
use App\Models\Department\Department;

class DepartmentController extends Controller
{
    public function showAll(): DepartmentCollection
    {
        return new DepartmentCollection(Department::all());
    }
}
