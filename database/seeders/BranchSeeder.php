<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $branches = [
            [
                'name' => 'SoftWare',
                'fingerprint_scanner_ip' => '192.168.2.202'
            ]
        ];

        DB::table('branches')->insert($branches);
    }
}
