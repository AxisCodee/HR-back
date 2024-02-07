<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class StudySituationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('study_situations')->insert([
            [
                'user_id' => 1,
                'degree' => 'Bachelor of Science',
                'study' => 'Computer Science',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 2,
                'degree' => 'Master of Business Administration',
                'study' => 'Business Administration',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 3,
                'degree' => 'Bachelor of Arts',
                'study' => 'English Literature',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 4,
                'degree' => 'Doctor of Medicine',
                'study' => 'Medicine',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 5,
                'degree' => 'Bachelor of Engineering',
                'study' => 'Mechanical Engineering',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
