<?php


namespace App\Services;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;

class UserService
{
    public function createUser($params, $role_name = false)
    {
        $user = User::create($params);
        if ($role_name) {
            $role = Role::where('name', $role_name)->first();
            $user->assignRole($role);
        }
        return $user;
    }
}
