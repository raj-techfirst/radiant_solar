<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class CreateAdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // $user = User::create([
        //     'name' => 'Owner', 
        //     'email' => 'main@admin.com',
        //     'password' => bcrypt('12345678'),
        //     'status' => '1',
        //     'otp' => '0',
        // ]);
        
        // $role = Role::create(['name' => 'Owner']);
        // $permissions = Permission::where('title_tag','Role')->pluck('id','id')->all();
        // $role->syncPermissions($permissions);
        // $user->assignRole([$role->id]);
    }
}
