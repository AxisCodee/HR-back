<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CertificateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            DB::table('certificates')->insert([
                'user_id' => rand(1, 10), // Replace with actual user IDs
                'content' => "Certificate content for entry $i",
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
