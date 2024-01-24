<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert
        ([
            [
                'email' => 'samy156@gmail.com',
                'password' => Hash::make('password'),
                'first_name' => 'samy',
                'middle_name'=> 'ahmad',
                'last_name' => 'mhmd',
                'role' => 'admin',
                'department_id' => null,
                'specialization' => 'spa',
                'pin' => 1,
            ],
            [
                'email' => 'samy1@gmail.com',
                'password' => Hash::make('password'),
                'first_name' => 'samy1',
                'middle_name'=> 'ahmad1',
                'last_name' => 'mhmd1',
                'role' => 'project_manager',
                'department_id' => null,
                'specialization' => 'spa',
                'pin' => 2,
            ],
            [
                'email' => 'samy2@gmail.com',
                'password' => Hash::make('password'),
                'first_name' => 'samy2',
                'middle_name'=> 'ahmad2',
                'last_name' => 'mhmd2',
                'role' => 'project_manager',
                'department_id' => null,
                'specialization' => 'spa',
                'pin' => 3,
            ],
            [
                'email' => 'samy3@gmail.com',
                'password' => Hash::make('password'),
                'first_name' => 'samy3',
                'middle_name'=> 'ahmad3',
                'last_name' => 'mhmd3',
                'role' => 'team_leader',
                'department_id' => 2,
                'specialization' => 'spa',
                'pin' => 4,
            ],
            [
                'email' => 'samy4@gmail.com',
                'password' => Hash::make('password'),
                'first_name' => 'samy4',
                'middle_name'=> 'ahmad4',
                'last_name' => 'mhmd4',
                'role' => 'employee',
                'department_id' => 2,
                'specialization' => 'spa',
                'pin' => 5,
            ],
            [
                'email' => 'samy5@gmail.com',
                'password' => Hash::make('password'),
                'first_name' => 'samy5',
                'middle_name'=> 'ahmad5',
                'last_name' => 'mhmd5',
                'role' => 'employee',
                'department_id' => 2,
                'specialization' => 'spa',
                'pin' => 6,
            ],
            [
                'email' => 'samy6@gmail.com',
                'password' => Hash::make('password'),
                'first_name' => 'samy6',
                'middle_name'=> 'ahmad6',
                'last_name' => 'mhmd6',
                'role' => 'employee',
                'department_id' => 2,
                'specialization' => 'spa',
                'pin' => 7,
            ],
            [
                'email' => 'samy7@gmail.com',
                'password' => Hash::make('password'),
                'first_name' => 'samy7',
                'middle_name'=> 'ahmad7',
                'last_name' => 'mhmd7',
                'role' => 'team_leader',
                'department_id' => 3,
                'specialization' => 'spa',
                'pin' => 8,
            ],
            [
                'email' => 'samy8@gmail.com',
                'password' => Hash::make('password'),
                'first_name' => 'samy8',
                'middle_name'=> 'ahmad8',
                'last_name' => 'mhmd8',
                'role' => 'employee',
                'department_id' => 3,
                'specialization' => 'spa',
                'pin' => 9,
            ],
            [
                'email' => 'samy9@gmail.com',
                'password' => Hash::make('password'),
                'first_name' => 'samy9',
                'middle_name'=> 'ahmad9',
                'last_name' => 'mhmd9',
                'role' => 'employee',
                'department_id' => 3,
                'specialization' => 'spa',
                'pin' => 10,
            ],

        ]);
    }
}
