<?php

namespace App\Services;

use Spatie\Permission\Models\Role;

class RoleService
{
    function createRole(array $params)
    {
        $role = Role::create($params);
        return $role;
    }

}
