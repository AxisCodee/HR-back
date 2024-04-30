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
    {
        DB::table('departments')->insert
        ([
                [
                    'name' => 'Back-End',
                    'branch_id' => 1
                ],
                [
                    'name' => 'Front-End',
                    'branch_id' => 1
                ],
                [
                    'name' => 'Mobile',
                    'branch_id' => 1
                ],
                [
                    'name' => 'UX/UI',
                    'branch_id' => 1
                ],
                [
                    'name' => 'Training',
                    'branch_id' => 1
                ],
            ]
        );
    }
}
