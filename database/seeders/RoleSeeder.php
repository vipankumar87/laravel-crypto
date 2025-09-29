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
        // Create permissions
        $permissions = [
            'manage users',
            'manage wallets',
            'manage investments',
            'view reports',
            'manage system',
            'manage referrals',
            'view dashboard',
            'invest funds',
            'withdraw funds',
            'view wallet'
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create System User role (super admin)
        $systemRole = Role::firstOrCreate(['name' => 'system']);
        $systemRole->syncPermissions($permissions); // Give all permissions

        // Create Admin role
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->syncPermissions([
            'manage users',
            'manage wallets',
            'manage investments',
            'view reports',
            'manage referrals',
            'view dashboard'
        ]);

        // Create User role (normal user)
        $userRole = Role::firstOrCreate(['name' => 'user']);
        $userRole->syncPermissions([
            'view dashboard',
            'invest funds',
            'withdraw funds',
            'view wallet',
            'manage referrals'
        ]);
    }
}
