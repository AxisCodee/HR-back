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
        $this->call(BranchSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(RequestSeeder::class);
        $this->call(ContractSeeder::class);
        $this->call(UserInfoSeeder::class);
        $this->call(CalendarsSeeder::class);
        $this->call(PermissionSeeder::class);
        $this->call(PolicySeeder::class);

        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
