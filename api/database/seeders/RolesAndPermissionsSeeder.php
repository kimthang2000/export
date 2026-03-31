<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'create_post',
            'edit_post',
            'delete_post',
            'view_post'
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $memberRole = Role::firstOrCreate(['name' => 'member']);

        // Admin gets all permissions
        $adminRole->permissions()->sync(Permission::all());

        // Member gets only view_post
        $viewPostPermission = Permission::where('name', 'view_post')->first();
        if ($viewPostPermission) {
            $memberRole->permissions()->sync([$viewPostPermission->id]);
        }
    }
}
