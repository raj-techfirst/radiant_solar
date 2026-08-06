<?php

namespace Database\Seeders;

use App\Models\RoleMaster;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Helper;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            PermissionSeeder::class,
            // CreateSuperAdminSeeder::class,
            // CreateAdminUserSeeder::class,
        ]);
    }
}
