<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function list(Request $request)
    {
        $roles = Role::query()->orderBy('name')->get();

        return response()->json(['roles' => $roles]);
    }
}
