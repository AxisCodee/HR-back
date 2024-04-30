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
                'email' => 'ismail@mail.com',
                'password' => Hash::make('password'),
                'first_name' => 'اسماعيل',
                'last_name' => 'عرنجي',
                'role' => 'admin',
                'specialization' => 'Mobile',
                'pin' => 1,
                'branch_id' => 1
            ],
            [
                'email' => 'dani@mail.com',
                'password' => Hash::make('password'),
                'first_name' => 'دانييل',
                'last_name' => 'فرنسيس',
                'role' => 'team_leader',
                'specialization' => 'Front-End',
                'pin' => 2,
                'branch_id' => 1
            ],
            [
                'email' => 'abdalrahman@mail.com',
                'password' => Hash::make('password'),
                'first_name' => 'عبد الرحمن',
                'last_name' => 'خدام الجامع',
                'role' => 'employee',
                'specialization' => 'Front-End',
                'pin' => 4,
                'branch_id' => 1
            ],
            [
                'email' => 'laith@mail.com',
                'password' => Hash::make('password'),
                'first_name' => 'ليث',
                'last_name' => 'خيربك',
                'role' => 'employee',
                'specialization' => 'Training',
                'pin' => 5,
                'branch_id' => 1
            ],
            [
                'email' => 'ali@mail.com',
                'password' => Hash::make('password'),
                'first_name' => 'علي',
                'last_name' => 'أسد',
                'role' => 'employee',
                'specialization' => 'Back-End',
                'pin' => 6,
                'branch_id' => 1
            ],
            [
                'email' => 'hussam@mail.com',
                'password' => Hash::make('password'),
                'first_name' => 'حسام',
                'last_name' => 'الزعبي',
                'role' => 'employee',
                'specialization' => 'Design',
                'pin' => 8,
                'branch_id' => 1
            ],
            [
                'email' => 'thalees@mail.com',
                'password' => Hash::make('password'),
                'first_name' => 'طاليس',
                'last_name' => 'مصطفى',
                'role' => 'team_leader',
                'specialization' => 'Back-End',
                'pin' => 10,
                'branch_id' => 1
            ],
            [
                'email' => 'raneem@mail.com',
                'password' => Hash::make('password'),
                'first_name' => 'رنيم',
                'last_name' => 'المرعوني',
                'role' => 'employee',
                'specialization' => 'Training',
                'pin' => 12,
                'branch_id' => 1
            ],
            [
                'email' => 'hadi@mail.com',
                'password' => Hash::make('password'),
                'first_name' => 'عبد الهادي',
                'last_name' => 'حمودة',
                'role' => 'team_leader',
                'specialization' => 'Design',
                'pin' => 13,
                'branch_id' => 1
            ],
            [
                'email' => 'nour@mail.com',
                'password' => Hash::make('password'),
                'first_name' => 'نور الهدى',
                'last_name' => 'موسى',
                'role' => 'employee',
                'specialization' => 'Mobile',
                'pin' => 14,
                'branch_id' => 1
            ],
            [
                'email' => 'yazan@mail.com',
                'password' => Hash::make('password'),
                'first_name' => 'يزن',
                'last_name' => 'الحوري',
                'role' => 'employee',
                'specialization' => 'Design',
                'pin' => 16,
                'branch_id' => 1
            ],
            [
                'email' => 'kamal@mail.com',
                'password' => Hash::make('password'),
                'first_name' => 'كمال',
                'last_name' => 'بدران',
                'role' => 'admin',
                'specialization' => 'Management',
                'pin' => 17,
                'branch_id' => 1
            ],
            [
                'email' => 'manager@mail.com',
                'password' => Hash::make('password'),
                'first_name' => 'Manager',
                'last_name' => 'Manager',
                'role' => 'admin',
                'specialization' => 'Management',
                'pin' => 17,
                'branch_id' => 1
            ],
        ]);
    }
}
