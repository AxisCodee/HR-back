<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RateSeeder extends Seeder
{
    public function run()
    {
        DB::table('rates')->insert([
            [
                'user_id' => 1,
                'evaluator_id' => 2,
                'rate_type_id' => 1,
                'date' => '2024-02-06',
                'rate' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 3,
                'evaluator_id' => 4,
                'rate_type_id' => 2,
                'date' => '2024-02-01',
                'rate' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Add more rows as needed...
        ]);
    }
}
