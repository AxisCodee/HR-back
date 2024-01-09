<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
        DB::table('users')->insert
        ([
        [
            'first_name' => 'esmail',
            'last_name' => 'last_name',
            'email' => 'esmail@gmail.com',
            'role_id' => '3',
            'department_id' => '3',
            'password' => Hash::make('123456789'),
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'first_name' => 'daniel',
            'last_name' => 'last_name',
            'email' => 'daniel@gmail.com',
            'role_id' => '2',
            'department_id' => '2',
            'password' => Hash::make('123456789'),
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'first_name' => 'Abdulrahman',
            'last_name' => 'last_name',
            'email' => 'Abdulrahman@gmail.com',
            'role_id' => '3',
            'department_id' => '2',
            'password' => Hash::make('123456789'),
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'first_name' => 'laith',
            'last_name' => 'last_name',
            'email' => 'laith@gmail.com',
            'role_id' => '4',
            'department_id' => '2',
            'password' => Hash::make('123456789'),
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'first_name' => 'ali',
            'last_name' => 'last_name',
            'email' => 'ali@gmail.com',
            'role_id' => '3',
            'department_id' => '1',
            'password' => Hash::make('123456789'),
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'first_name' => 'mazhar',
            'last_name' => 'last_name',
            'email' => 'mazhar@gmail.com',
            'role_id' => '4',
            'department_id' => '1',
            'password' => Hash::make('123456789'),
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'first_name' => 'hussam',
            'last_name' => 'last_name',
            'email' => 'hussam@gmail.com',
            'role_id' => '4',
            'department_id' => '4',
            'password' => Hash::make('123456789'),
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'first_name' => 'elfat',
            'last_name' => 'last_name',
            'email' => 'elfat@gmail.com',
            'role_id' => '4',
            'department_id' => '4',
            'password' => Hash::make('123456789'),
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'first_name' => 'mohammad',
            'last_name' => 'last_name',
            'email' => 'mohammad@gmail.com',
            'role_id' => '4',
            'department_id' => '2',
            'password' => Hash::make('123456789'),
            'created_at' => now(),
            'updated_at' => now(),
        ]
        ]
    );
    }
}
