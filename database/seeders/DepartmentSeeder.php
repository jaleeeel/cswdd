<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('departments')->insert([
            ['name' => 'Health Services', 'description' => 'Medical and health-related services'],
            ['name' => 'Social Services', 'description' => 'Community and social welfare programs'],
            ['name' => 'Education', 'description' => 'Educational programs and services'],
            ['name' => 'Finance', 'description' => 'Financial assistance and management'],
        ]);
    }
}
