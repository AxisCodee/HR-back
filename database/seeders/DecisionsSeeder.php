<?php

namespace Database\Seeders;


use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DecisionsSeeder extends Seeder
{
    public function run()
    {
        DB::table('decisions')->insert([
            [
                'user_id' => 1,
                'type' => 'warning',
                'content' => 'Late arrival to work',
                'amount' => null,
                'dateTime' => '2024-02-06 09:15:00',
                'salary' => null,
                'fromSystem' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 2,
                'type' => 'reward',
                'content' => 'Employee of the month',
                'amount' => 500,
                'dateTime' => '2024-02-01 14:30:00',
                'salary' => null,
                'fromSystem' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
        ]);
    }
}
