<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Generate strong passwords for each user type
        $systemPassword = 'Sy$tem@2025!Admin#123';
        $adminPassword = 'Admin@123';
        $userPassword = 'Admin@123';
        
        // Create System User
        $systemUser = User::firstOrCreate([
            'username' => 'system'
        ], [
            'name' => 'System Administrator',
            'email' => 'system@cryptoapp.com',
            'password' => Hash::make($systemPassword),
            'encrypted_password' => encrypt($systemPassword),
            'email_verified_at' => now(),
        ]);
        $systemUser->assignRole('system');

        // Create Admin User
        $adminUser = User::firstOrCreate([
            'username' => 'admin'
        ], [
            'name' => 'Admin User',
            'email' => 'admin@cryptoapp.com',
            'password' => Hash::make($adminPassword),
            'encrypted_password' => encrypt($adminPassword),
            'email_verified_at' => now(),
        ]);
        $adminUser->assignRole('admin');

        // Create Normal User
        $normalUser = User::firstOrCreate([
            'username' => 'user'
        ], [
            'name' => 'Normal User',
            'email' => 'user@cryptoapp.com',
            'password' => Hash::make($userPassword),
            'encrypted_password' => encrypt($userPassword),
            'email_verified_at' => now(),
        ]);
        $normalUser->assignRole('user');

        // Update existing users without encrypted passwords with a strong default password
        // User::whereNull('encrypted_password')->each(function ($user) {
        //     // $strongDefaultPassword = 'Cr#pt0@' . date('Y') . '!User$' . rand(1000, 9999);
        //     // $user->setEncryptedPassword($strongDefaultPassword);
        // });
    }
}
