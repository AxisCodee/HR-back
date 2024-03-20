<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserInfoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $genders = ['Male', 'Female'];
        $social_situations = ['Single', 'Married'];
        $military_situations = ['Postponed', 'Exempt', 'Finished'];
        $levels = ['Senior', 'Mid', 'Junior'];

        for ($i = 1; $i <= 17; $i++) {
            DB::table('user_infos')->insert([
                'user_id' => $i,
                'image' => Str::random(10),
                'birth_date' => date('Y-m-d', mt_rand(strtotime('1980-01-01'), strtotime('2000-12-31'))),
                'start_date' => date('Y-m-d', mt_rand(strtotime('2010-01-01'), strtotime('2020-12-31'))),
                'gender' => $genders[array_rand($genders)],
                'nationalID' => Str::random(10),
                'social_situation' => $social_situations[array_rand($social_situations)],
                'military_situation' => $military_situations[array_rand($military_situations)],
                'level' => $levels[array_rand($levels)],
                'health_status' => Str::random(10),
                'salary' => rand(1000, 5000),
                'compensation_hours' => rand(0, 100),
            ]);
        }
    }
}
