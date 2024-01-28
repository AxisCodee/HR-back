<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserInfoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('user_infos')->insert(
            [
                'user_id' => 1, // Replace with the actual user ID
                'image' => 'null', // You can set an image URL here if needed
                'birth_date' => '1990-01-15', // Example birth date
                'gender' => 'Male', // Example gender
                'nationalID' => 1234567890, // Example national ID
                'social_situation' => 'Married', // Example social situation
                'military_situation' => 'Postponed', // Example military situation
                'salary' => 97777, // Example salary
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 2, // Replace with the actual user ID
                'image' => 'null', // You can set an image URL here if needed
                'birth_date' => '1990-01-15', // Example birth date
                'gender' => 'Male', // Example gender
                'nationalID' => 1234567890, // Example national ID
                'social_situation' => 'Married', // Example social situation
                'military_situation' => 'Postponed', // Example military situation
                'salary' => 97777, // Example salary
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 3, // Replace with the actual user ID
                'image' => 'null', // You can set an image URL here if needed
                'birth_date' => '1990-01-15', // Example birth date
                'gender' => 'Male', // Example gender
                'nationalID' => 1234567890, // Example national ID
                'social_situation' => 'Married', // Example social situation
                'military_situation' => 'Postponed', // Example military situation
                'salary' => 97777, // Example salary
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );
    }
}
