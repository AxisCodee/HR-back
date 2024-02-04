<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $this->call(DepartmentSeeder::class);
        $this->call(RoleSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(RequestSeeder::class);
        $this->call(ContractSeeder::class);
        $this->call(UserInfoSeeder::class);
        $this->call(CalendarsSeeder::class);
<<<<<<< HEAD
        $this->call(PermissionSeeder::class);
=======
        $this->call(BranchSeeder::class);
>>>>>>> 314b2b4908c2836f6513e653e822d9c0f7b0badc

        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
