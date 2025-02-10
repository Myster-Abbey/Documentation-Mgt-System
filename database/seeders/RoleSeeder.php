<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $admin = Role::create(['name' => 'admin']);
        $user = Role::create(['name' => 'user']);

        Permission::create(['name' => 'view_files']);
        Permission::create(['name' => 'upload_documents']);
        Permission::create(['name' => 'manage_users']);

        $admin->givePermissionTo(Permission::all());
        $user->givePermissionTo('upload_documents');
    }
}
