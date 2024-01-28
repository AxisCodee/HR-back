<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserInfosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userInfos = [
            [
                'user_id' => 1,
                'image' => 'path/to/image1.jpg',
                'birth_date' => '1990-01-01',
                'gender' => 'Male',
                'nationalID' => 1234567890,
                'social_situation' => 'Married',
                'military_situation' => 'Postponed',
                'salary' => 5000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 2,
                'image' => 'path/to/image2.jpg',
                'birth_date' => '1995-05-05',
                'gender' => 'Female',
                'nationalID' => 9876543210,
                'social_situation' => 'Single',
                'military_situation' => 'Finished',
                'salary' => 6000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Add more seed data as needed
        ];

        DB::table('user_infos')->insert($userInfos);
    }
}

