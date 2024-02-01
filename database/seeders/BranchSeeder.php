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
            ],
            [
                'name' => 'Branch 2',
            ],
            [
                'name' => 'Branch 3',
            ],
            [
                'name' => 'Branch 4',
            ],
            [
                'name' => 'Branch 5',
            ],
        ];

        DB::table('branches')->insert($branches);
    }
}
