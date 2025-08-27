<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('roles')->insert([
            ['id' => 1, 'name' => 'SuperAdmin', 'description' => 'Full system access'],
            ['id' => 2, 'name' => 'Admin', 'description' => 'Department level access'],
            ['id' => 3, 'name' => 'Staff', 'description' => 'Encode and CRUD data'],
        ]);
    }
}
