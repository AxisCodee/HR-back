<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('roles')->insert
        ([
        [
            'first_name' => 'esmail',
            'email' => 'esmail@gmail.com',
            'role_id' => '3',
            'department_id' => '3',
            'password' => '123'
        ],
        [
            'first_name' => 'daniel',
            'email' => 'daniel@gmail.com',
            'role_id' => '2',
            'department_id' => '2',
            'password' => '123'
        ],
        [
            'first_name' => 'Abdulrahman',
            'email' => 'Abdulrahman@gmail.com',
            'role_id' => '3',
            'department_id' => '2',
            'password' => '123'
        ],
        [
            'first_name' => 'laith',
            'email' => 'laith@gmail.com',
            'role_id' => '4',
            'department_id' => '2',
            'password' => '123'
        ],
        [
            'first_name' => 'ali',
            'email' => 'ali@gmail.com',
            'role_id' => '3',
            'department_id' => '1',
            'password' => '123'
        ],
        [
            'first_name' => 'mazhar',
            'email' => 'mazhar@gmail.com',
            'role_id' => '4',
            'department_id' => '1',
            'password' => '123'
        ],
        [
            'first_name' => 'hussam',
            'email' => 'hussam@gmail.com',
            'role_id' => '4',
            'department_id' => '4',
            'password' => '123'
        ],
        [
            'first_name' => 'elfat',
            'email' => 'elfat@gmail.com',
            'role_id' => '4',
            'department_id' => '4',
            'password' => '123'
        ],
        [
            'first_name' => 'mazhar',
            'email' => 'mazhar@gmail.com',
            'role_id' => '4',
            'department_id' => '1',
            'password' => '123'
        ],
        [
            'first_name' => 'mohammad',
            'email' => 'mohammad@gmail.com',
            'role_id' => '4',
            'department_id' => '2',
            'password' => '123'
        ],
        ]
    );
    }
}
