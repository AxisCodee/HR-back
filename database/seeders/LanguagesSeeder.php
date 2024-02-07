<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LanguagesSeeder extends Seeder
{
    public function run()
    {
        DB::table('languages')->insert([
            [
                'user_id' => 1,
                'name' => 'English',
                'rate' => '5',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 2,
                'name' => 'Arabic',
                'rate' => '4',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 3,
                'name' => 'French',
                'rate' => '3',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 4,
                'name' => 'German',
                'rate' => '4',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 5,
                'name' => 'Japanese',
                'rate' => '5',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Add more rows as needed...
        ]);
    }
}
