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

        DB::table('users')->insert([
            [
                'email' => 'ismaeel@gmail.com',
                'password' => Hash::make('password'),
                'first_name' => 'ismaeel',
                'middle_name' => 'tttt',
                'last_name' => 'aarangy',
                'role' => 'admin',
                'specialization' => 'spa',
                'pin' => 1,
                'branch_id'=>1
            ],
            [
                'email' => 'dani@gmail.com',
                'password' => Hash::make('password'),
                'first_name' => 'dani',
                'middle_name' => 'tttt',
                'last_name' => 'yyyy',
                'role' => 'employee',
                'specialization' => 'spa',
                'pin' => 2,
                'branch_id'=>1
            ],
            [
                'email' => 'mhmd@gmail.com',
                'password' => Hash::make('password'),
                'first_name' => 'mhmd',
                'middle_name' => 'tttt',
                'last_name' => 'yyyy',
                'role' => 'employee',
                'specialization' => 'spa',
                'pin' => 3,
                'branch_id'=>1
            ],
            [
                'email' => 'abdalrahman@gmail.com',
                'password' => Hash::make('password'),
                'first_name' => 'abdalrahman',
                'middle_name' => 'tttt',
                'last_name' => 'yyyy',
                'role' => 'employee',
                'specialization' => 'spa',
                'pin' => 4,
                'branch_id'=>1
            ],
            [
                'email' => 'laith@gmail.com',
                'password' => Hash::make('password'),
                'first_name' => 'laith',
                'middle_name' => 'tttt',
                'last_name' => 'yyyy',
                'role' => 'employee',
                'specialization' => 'spa',
                'pin' => 5,
                'branch_id'=>1
            ],
            [
                'email' => 'ali@gmail.com',
                'password' => Hash::make('password'),
                'first_name' => 'ali',
                'middle_name' => 'tttt',
                'last_name' => 'yyyy',
                'role' => 'employee',
                'specialization' => 'spa',
                'pin' => 6,
                'branch_id'=>1
            ],
            [
                'email' => 'mazhar@gmail.com',
                'password' => Hash::make('password'),
                'first_name' => 'mazhar',
                'middle_name' => 'tttt',
                'last_name' => 'yyyy',
                'role' => 'employee',
                'specialization' => 'spa',
                'pin' => 7,
                'branch_id'=>1
            ],
            [
                'email' => 'hussam@gmail.com',
                'password' => Hash::make('password'),
                'first_name' => 'hussam',
                'middle_name' => 'tttt',
                'last_name' => 'yyyy',
                'role' => 'employee',
                'specialization' => 'spa',
                'pin' => 8,
                'branch_id'=>1
            ],
            [
                'email' => 'elfat@gmail.com',
                'password' => Hash::make('password'),
                'first_name' => 'elfat',
                'middle_name' => 'tttt',
                'last_name' => 'yyyy',
                'role' => 'employee',
                'specialization' => 'spa',
                'pin' => 9,
                'branch_id'=>1
            ],
            [
                'email' => 'thalees@gmail.com',
                'password' => Hash::make('password'),
                'first_name' => 'thalees',
                'middle_name' => 'tttt',
                'last_name' => 'yyyy',
                'role' => 'employee',
                'specialization' => 'spa',
                'pin' => 10,
                'branch_id'=>1
            ],
            [
                'email' => 'ghfran@gmail.com',
                'password' => Hash::make('password'),
                'first_name' => 'ghfran',
                'middle_name' => 'tttt',
                'last_name' => 'yyyy',
                'role' => 'employee',
                'specialization' => 'spa',
                'pin' => 11,
                'branch_id'=>1
            ],
            [
                'email' => 'raneem@gmail.com',
                'password' => Hash::make('password'),
                'first_name' => 'raneem',
                'middle_name' => 'tttt',
                'last_name' => 'yyyy',
                'role' => 'employee',
                'specialization' => 'spa',
                'pin' => 12,
                'branch_id'=>1
            ],
            [
                'email' => 'hadi@gmail.com',
                'password' => Hash::make('password'),
                'first_name' => 'hadi',
                'middle_name' => 'tttt',
                'last_name' => 'yyyy',
                'role' => 'employee',
                'specialization' => 'spa',
                'pin' => 13,
                'branch_id'=>1
            ],
            [
                'email' => 'noOne@gmail.com',   
                'password' => Hash::make('password'),
                'first_name' => 'noo',
                'middle_name' => 'tttt',
                'last_name' => 'yyyy',
                'role' => 'employee',
                'specialization' => 'spa',
                'pin' => 14,
                'branch_id'=>1
            ],
            [
                'email' => 'noOne1@gmail.com',
                'password' => Hash::make('password'),
                'first_name' => 'noo1',
                'middle_name' => 'tttt',
                'last_name' => 'yyyy',
                'role' => 'employee',
                'specialization' => 'spa',
                'pin' => 15,
                'branch_id'=>1
            ],


            [
                'email' => 'yazan@gmail.com',
                'password' => Hash::make('password'),
                'first_name' => 'yazan',
                'middle_name' => 'tttt',
                'last_name' => 'yyyy',
                'role' => 'employee',
                'specialization' => 'spa',
                'pin' => 16,
                'branch_id'=>1
            ],
            [
                'email' => 'kamal@gmail.com',
                'password' => Hash::make('password'),
                'first_name' => 'kamal',
                'middle_name' => 'tttt',
                'last_name' => 'yyyy',
                'role' => 'employee',
                'specialization' => 'spa',
                'pin' => 17,
                'branch_id'=>1
            ],
        ]);
    }
}
