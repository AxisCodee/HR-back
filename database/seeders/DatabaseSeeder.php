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

        $this->call(BranchSeeder::class);
        //     $this->call(DepartmentSeeder::class);
        $this->call(RoleSeeder::class);
        $this->call(DepartmentSeeder::class);
        $this->call(UserSeeder::class);
//        $this->call(UserInfoSeeder::class);
        //     $this->call(AbsencesSeeder::class);
        //     $this->call(CareersSeeder::class);
        //     $this->call(CertificateSeeder::class);
        //     $this->call(ContactSeeder::class);
        //     $this->call(DecisionsSeeder::class);
        //     $this->call(DepositsSeeder::class);
        //     $this->call(LanguagesSeeder::class);
        //     $this->call(NotesSeeder::class);
        //     $this->call(RateTypesSeeder::class);
        //     $this->call(RateSeeder::class);
        //     $this->call(ReportsSeeder::class);
        //     $this->call(SkillsSeeder::class);
        //     $this->call(SalarySeeder::class);
        //     $this->call(StudySituationSeeder::class);
        //     $this->call(RequestSeeder::class);
        //     $this->call(ContractSeeder::class);
        //     $this->call(UserInfoSeeder::class);
        //     $this->call(CalendarsSeeder::class);
        //     $this->call(PermissionSeeder::class);
        //    //$this->call(PolicySeeder::class);
        //     $this->call(EmpOfMonthSeeder::class);

        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
