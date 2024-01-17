<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContractSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('contracts')->insert
        ([
            [
                'path' => 'C:/files/dasa.pdf',
                'endTime' => '2026-10-12 12:12:10',
                'user_id' => 1,
            ],
            [
                'path' => 'Casdaasdac',
                'endTime' => '2026-10-12 12:12:10',
                'user_id' => 1,
            ],
            [
                'path' => 'C:/files/dasa.pdf',
                'endTime' => '2026-10-12 12:12:10',
                'user_id' => 1,
            ],
            [
                'path' => 'C:/files/dasa.pdf',
                'endTime' => '2026-10-12 12:12:10',
                'user_id' => 1,
            ],
            [
                'path' => 'C:/files/dasa.pdf',
                'endTime' => '2026-10-12 12:12:10',
                'user_id' => 1,
            ],
            [
                'path' => 'C:/files/dasa.pdf',
                'endTime' => '2026-10-12 12:12:10',
                'user_id' => 1,
            ],
            [
                'path' => 'C:/files/dasa.pdf',
                'endTime' => '2026-10-12 12:12:10',
                'user_id' => 1,
            ],
            [
                'path' => 'C:/files/dasa.pdf',
                'endTime' => '2026-10-12 12:12:10',
                'user_id' => 1,
            ],
            [
                'path' => 'C:/files/dasa.pdf',
                'endTime' => '2026-10-12 12:12:10',
                'user_id' => 1,
            ],
            [
                'path' => 'C:/files/dasa.pdf',
                'endTime' => '2026-10-12 12:12:10',
                'user_id' => 1,
            ],
        ]);
    }
}
