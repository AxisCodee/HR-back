<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {DB::table('departments')->insert
        ([[
            'name' => 'Back_End',
            'branch_id'=>1
        ],
        [
            'name' => 'Front_End',
            'branch_id'=>1
        ],
        [
            'name' => 'Mobile',
            'branch_id'=>2
        ],
        [
            'name' => 'UI_UX',
            'branch_id'=>2
        ],]
    );
    }
}
