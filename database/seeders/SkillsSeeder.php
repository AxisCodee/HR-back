<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SkillsSeeder extends Seeder
{
    public function run()
    {
        DB::table('skills')->insert([
            [
                'user_id' => 1,
                'name' => 'PHP',
                'rate' => '4',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 2,
                'name' => 'JavaScript',
                'rate' => '5',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 3,
                'name' => 'Python',
                'rate' => '4',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 4,
                'name' => 'Java',
                'rate' => '3',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 5,
                'name' => 'Ruby',
                'rate' => '5',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
