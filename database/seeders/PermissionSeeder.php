<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define your permissions here
        $permissions = [
            [
                'name' => 'create users',
                'guard_name' => 'web',
            ],
            [
                'name' => 'edit users',
                'guard_name' => 'web',
            ],
            [
                'name' => 'delete users',
                'guard_name' => 'web',
            ],
            [
                'name' => 'create posts',
                'guard_name' => 'web',
            ],
            [
                'name' => 'edit posts',
                'guard_name' => 'web',
            ],
            [
                'name' => 'delete posts',
                'guard_name' => 'web',
            ],
        ];

        foreach ($permissions as $permissionData) {
            Permission::create($permissionData);
        }
    }
}

