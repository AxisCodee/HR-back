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
<<<<<<< HEAD
        DB::table('users')->insert([
            [
                'email' => 'samy156@gmail.com',
                'password' => Hash::make('password'),
                'first_name' => 'samy',
                'middle_name' => 'ahmad',
                'last_name' => 'mhmd',
                'role' => 'admin',
                'department_id' => null,
                'specialization' => 'spa',
                'pin' => 1,
                'branch_id' => 1,
            ],
        ]);

=======

        DB::table('users')->insert([
            [
                'email' => 'admin@gmail.com',
                'password' => Hash::make('password'),
                'first_name' => 'admin',
                'middle_name' => 'admin',
                'last_name' => 'admin',
                'role' => 'admin',
                'specialization' => 'spa',
                'pin' => 1,
            ],
        ]);
>>>>>>> edb0ac1d027ae417beceb09c2d8fb0ac49bd9270
    }
}
