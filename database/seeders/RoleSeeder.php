<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;



class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::create(['name' => 'admin','guard_name'=>'api']);
        $userRole = Role::create(['name' => 'team_leader','guard_name'=>'api']);
        $userRole = Role::create(['name' => 'employee','guard_name'=>'api']);
    }
}
