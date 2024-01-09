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
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'password' => Hash::make('secret'),
            'first_name' => 'John',
            'last_name' => 'Doe',
            'role' => 'admin',
            'department_id' => 1,
            'pin' => 1234,
        ]);
    }
}
