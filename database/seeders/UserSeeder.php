<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            [
                'email' => 'ismail@gmail.com',
                'password' => Hash::make('password'),
                'first_name' => 'اسماعيل',
                'last_name' => 'عرنجي',
                'role' => 'employee',
                'department_id' => 3,
                'specialization' => 'Mobile',
                'pin' => 1,
                'branch_id' => 1
            ],
            [
                'email' => 'dani@gmail.com',
                'password' => Hash::make('password'),
                'first_name' => 'دانييل',
                'last_name' => 'فرنسيس',
                'role' => 'employee',
                'department_id' => 2,
                'specialization' => 'Front-End',
                'pin' => 2,
                'branch_id' => 1
            ],
            [
                'email' => 'abdalrahman@gmail.com',
                'password' => Hash::make('password'),
                'first_name' => 'عبد الرحمن',
                'last_name' => 'خدام الجامع',
                'role' => 'employee',
                'department_id' => 2,
                'specialization' => 'Front-End',
                'pin' => 4,
                'branch_id' => 1
            ],
            [
                'email' => 'laith@gmail.com',
                'password' => Hash::make('password'),
                'first_name' => 'ليث',
                'last_name' => 'خيربك',
                'role' => 'employee',
                'department_id' => 5,
                'specialization' => 'Training',
                'pin' => 5,
                'branch_id' => 1
            ],
            [
                'email' => 'ali@gmail.com',
                'password' => Hash::make('password'),
                'first_name' => 'علي',
                'last_name' => 'أسد',
                'role' => 'employee',
                'department_id' => 1,
                'specialization' => 'Back-End',
                'pin' => 6,
                'branch_id' => 1
            ],
            [
                'email' => 'hussam@gmail.com',
                'password' => Hash::make('password'),
                'first_name' => 'حسام',
                'last_name' => 'الزعبي',
                'role' => 'employee',
                'department_id' => 4,
                'specialization' => 'UX/UI',
                'pin' => 8,
                'branch_id' => 1
            ],
            [
                'email' => 'thalees@gmail.com',
                'password' => Hash::make('password'),
                'first_name' => 'طاليس',
                'last_name' => 'مصطفى',
                'role' => 'employee',
                'department_id' => 1,
                'specialization' => 'Back-End',
                'pin' => 10,
                'branch_id' => 1
            ],
            [
                'email' => 'raneem@gmail.com',
                'password' => Hash::make('password'),
                'first_name' => 'رنيم',
                'last_name' => 'المرعوني',
                'role' => 'employee',
                'department_id' => 5,
                'specialization' => 'Training',
                'pin' => 12,
                'branch_id' => 1
            ],
            [
                'email' => 'hadi@gmail.com',
                'password' => Hash::make('password'),
                'first_name' => 'عبد الهادي',
                'last_name' => 'حمودة',
                'role' => 'employee',
                'department_id' => 4,
                'specialization' => 'UX/UI',
                'pin' => 13,
                'branch_id' => 1
            ],
            [
                'email' => 'nour@gmail.com',
                'password' => Hash::make('password'),
                'first_name' => 'نور الهدى',
                'last_name' => 'موسى',
                'role' => 'employee',
                'department_id' => 3,
                'specialization' => 'Mobile',
                'pin' => 14,
                'branch_id' => 1
            ],
            [
                'email' => 'yazan@gmail.com',
                'password' => Hash::make('password'),
                'first_name' => 'يزن',
                'last_name' => 'الحوري',
                'role' => 'employee',
                'department_id' => 4,
                'specialization' => 'UX/UI',
                'pin' => 16,
                'branch_id' => 1
            ],
            [
                'email' => 'kamal@gmail.com',
                'password' => Hash::make('password'),
                'first_name' => 'كمال',
                'last_name' => 'بدران',
                'role' => 'employee',
                'department_id' => 1,
                'specialization' => 'Back-End',
                'pin' => 17,
                'branch_id' => 1
            ], [
                'email' => 'kamalManager@gmail.com',
                'password' => Hash::make('password'),
                'first_name' => 'كمال',
                'last_name' => 'بدران',
                'role' => 'admin',
                'department_id' => 1,
                'specialization' => 'Management',
                'pin' => 9999,
                'branch_id' => 1
            ],
            [
                'email' => 'manager@gmail.com',
                'password' => Hash::make('password'),
                'first_name' => 'Manager',
                'last_name' => 'Manager',
                'role' => 'admin',
                'department_id' => 1,
                'specialization' => 'Management',
                'pin' => 999,
                'branch_id' => 1
            ],
        ]);
    }
}
