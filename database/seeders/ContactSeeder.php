<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContactSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('contacts')->insert([
            [
                'user_id' => 1,
                'type' => 'emergency',
                'email' => 'emergency@example.com',
                'phone_num' => '123-456-7890',
                'name' => 'John Doe',
                'address' => '123 Main St',
                'contact' => 'Emergency Contact',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 1,
                'type' => 'normal',
                'email' => 'normal@example.com',
                'phone_num' => '987-654-3210',
                'name' => 'Jane Smith',
                'address' => '456 Elm Ave',
                'contact' => 'Normal Contact',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Add more sample data as needed
        ]);
    }
}
