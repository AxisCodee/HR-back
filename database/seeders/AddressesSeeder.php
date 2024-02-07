<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AddressesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            DB::table('addresses')->insert([
                'title' => "Address $i",
                'city' => 'Sample City',
                'user_id' => rand(1, 10), // Replace with actual user IDs
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
