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
                'name' => 'Branch 1',
                'fingerprint_scanner_ip' => '192.168.2.202'
            ],
            [
                'name' => 'Branch 2',
                'fingerprint_scanner_ip' => '192.168.2.202'
            ],
            [
                'name' => 'Branch 3',
                'fingerprint_scanner_ip' => '192.168.2.202'
            ],
            [
                'name' => 'Branch 4',
                'fingerprint_scanner_ip' => '192.168.2.202'
            ],
            [
                'name' => 'Branch 5',
                'fingerprint_scanner_ip' => '192.168.2.202'
            ],
        ];

        DB::table('branches')->insert($branches);
    }
}
