<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RateTypesSeeder extends Seeder
{
    public function run()
    {
        DB::table('rate_types')->insert([
            [
                'branch_id' => 1,
                'rate_type' => 'Regular',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'branch_id' => 2,
                'rate_type' => 'Overtime',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'branch_id' => 3,
                'rate_type' => 'Holiday',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'branch_id' => 4,
                'rate_type' => 'Night Shift',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'branch_id' => 5,
                'rate_type' => 'Special Project',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Add more rows as needed...
        ]);
    }
}
