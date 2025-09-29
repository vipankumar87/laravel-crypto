<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Create permissions
        $permissions = [
            'access-user-features',
            'access-admin-features',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Assign permissions to roles
        $userRole = Role::where('name', 'user')->first();
        $adminRole = Role::where('name', 'admin')->first();
        $systemRole = Role::where('name', 'system')->first();

        if ($userRole) {
            $userRole->givePermissionTo('access-user-features');
        }

        if ($adminRole) {
            $adminRole->givePermissionTo('access-admin-features');
        }

        if ($systemRole) {
            $systemRole->givePermissionTo(['access-admin-features', 'access-user-features']);
        }
    }
}