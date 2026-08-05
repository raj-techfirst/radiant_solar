<?php

namespace App\Services;

use Spatie\Permission\Models\Permission;

class PermissionService
{
    public function createPermission(array $params)
    {
        $permission = Permission::create($params);
        return $permission;
    }
}
