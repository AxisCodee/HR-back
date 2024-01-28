<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class CalendarsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void

    {
                $calendars = [
                    [
                        'title' => 'Meeting 1',
                        'description' => 'This is the first meeting',
                        'start' => '2024-01-28 09:00:00',
                        'end' => '2024-01-28 10:00:00',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'title' => 'Meeting 2',
                        'description' => 'This is the second meeting',
                        'start' => '2024-01-29 14:30:00',
                        'end' => '2024-01-29 16:00:00',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    // Add more seed data as needed
                ];

                DB::table('calendars')->insert($calendars);
            }
        }


