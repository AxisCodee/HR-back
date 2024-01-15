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
            'last_name' => 'mhmd',
            'role' => 'admin',
            'department_id' => null,
            'pin' => 6,
            ],
            [
                'email' => 'samy1@gmail.com',
                'password' => Hash::make('password'),
                'first_name' => 'samy1',
                'last_name' => 'mhmd1',
                'role' => 'admin',
                'department_id' => null,
                'pin' => 7,
            ],
            [
                'email' => 'samy2@gmail.com',
                'password' => Hash::make('password'),
                'first_name' => 'samy2',
                'last_name' => 'mhmd2',
                'role' => 'admin',
                'department_id' => null,
                'pin' => 8,
            ],
            [
                'email' => 'samy3@gmail.com',
                'password' => Hash::make('password'),
                'first_name' => 'samy3',
                'last_name' => 'mhmd3',
                'role' => 'admin',
                'department_id' => null,
                'pin' => 9,
            ],
            [
                'email' => 'samy4@gmail.com',
                'password' => Hash::make('password'),
                'first_name' => 'samy4',
                'last_name' => 'mhmd4',
                'role' => 'admin',
                'department_id' => null,
                'pin' => 10,
            ],

        ]);
    }
}
