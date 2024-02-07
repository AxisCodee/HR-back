<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepositsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('deposits')->insert([
            [
                'user_id' => 1,
                'description' => 'Received salary deposit',
                'received_date' => '2024-02-06',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 2,
                'description' => 'Bonus payment',
                'received_date' => '2024-02-01',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Add more rows as needed...
        ]);
    }
}
