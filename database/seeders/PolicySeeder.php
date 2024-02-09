<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PolicySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('policies')->insert([
            'work_time' => json_encode([
                'work_days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'],
                'start_time' => '09:00 AM',
                'cut_off_time' => '12:00 PM',
                'end_time' => '05:00 PM',
                'notes' => ['Work hours details.', 'blabla'],
            ]),
            'annual_salary_increase' => json_encode(['percentage' => 5]),
            'warnings' => json_encode(['message' => 'Please read the policy carefully.']),
            'absence_management' => json_encode(['sick_days' => 10, 'vacation_days' => 15]),
            'deduction_status' => true,
            'branch_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ],);
    }
}
