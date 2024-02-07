<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SalarySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('user_salaries')->insert([
            'date' => Str::random(10), // Replace with actual date values
            'salary' => 50000.0, // Replace with actual salary values
            'user_id' => 1, // Replace with the appropriate user ID
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
