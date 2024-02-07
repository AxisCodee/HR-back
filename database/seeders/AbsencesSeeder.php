<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AbsencesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('absences')->insert([
            [
                'type' => 'justified',
                'user_id' => 1,
                'startDate' => '2024-05-01',
                'endDate' => '2024-05-03',
                'duration' => 'daily',
                'status' => 'accepted',
                'hours_num' => null,
                'dayNumber' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'type' => 'Unjustified',
                'user_id' => 2,
                'startDate' => '2024-05-01',
                'endDate' => '2024-05-03',
                'duration' => 'daily',
                'status' => 'accepted',
                'hours_num' => null,
                'dayNumber' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ], [
                'type' => 'null',
                'user_id' => 3,
                'startDate' => '2024-05-01',
                'endDate' => '2024-05-03',
                'duration' => 'daily',
                'status' => 'accepted',
                'hours_num' => null,
                'dayNumber' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ], [
                'type' => 'justified',
                'user_id' => 4,
                'startDate' => '2024-05-01',
                'endDate' => '2024-05-03',
                'duration' => 'daily',
                'status' => 'accepted',
                'hours_num' => null,
                'dayNumber' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
