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
            'work_time' => json_encode(['start' => '9:00 AM', 'end' => '5:00 PM']),
            'annual_salary_increase' => json_encode(['percentage' => 5]),
            'warnings' => json_encode(['message' => 'Please read the policy carefully.']),
            'absence_management' => json_encode(['sick_days' => 10, 'vacation_days' => 15]),
            'deduction_status' => true,
            'branch_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ], [
            'work_time' => json_encode(['start' => '9:00 AM', 'end' => '5:00 PM']),
            'annual_salary_increase' => json_encode(['percentage' => 5]),
            'warnings' => json_encode(['message' => 'Please read the policy carefully.']),
            'absence_management' => json_encode(['sick_days' => 10, 'vacation_days' => 15]),
            'deduction_status' => true,
            'branch_id' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ], [
            'work_time' => json_encode(['start' => '9:00 AM', 'end' => '5:00 PM']),
            'annual_salary_increase' => json_encode(['percentage' => 5]),
            'warnings' => json_encode(['message' => 'Please read the policy carefully.']),
            'absence_management' => json_encode(['sick_days' => 10, 'vacation_days' => 15]),
            'deduction_status' => true,
            'branch_id' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ], [
            'work_time' => json_encode(['start' => '9:00 AM', 'end' => '5:00 PM']),
            'annual_salary_increase' => json_encode(['percentage' => 5]),
            'warnings' => json_encode(['message' => 'Please read the policy carefully.']),
            'absence_management' => json_encode(['sick_days' => 10, 'vacation_days' => 15]),
            'deduction_status' => true,
            'branch_id' => 4,
            'created_at' => now(),
            'updated_at' => now(),
        ],);
    }
}
