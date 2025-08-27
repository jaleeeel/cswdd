<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $superAdminRole = Role::firstOrCreate(
            ['name' => 'SuperAdmin'],
            ['description' => 'Full system access']
        );

        User::firstOrCreate(
            ['email' => 'superadmin@system.com'], // ensure no duplicates
            [
                'name' => 'Super Administrator',
                'password' => Hash::make('password'),
                'role_id' => $superAdminRole->id,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
    }
}
