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
    }
}
