<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NotesSeeder extends Seeder
{
    public function run()
    {
        DB::table('notes')->insert([
            [
                'user_id' => 1,
                'content' => 'This is an important note about the project deadline.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 2,
                'content' => 'Remember to follow up with the client regarding their feedback.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 3,
                'content' => 'Meeting notes from yesterday: discussed new feature requirements.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 4,
                'content' => 'Note to self: review the code changes before merging.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 5,
                'content' => 'Important contact details for the upcoming conference.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Add more rows as needed...
        ]);
    }
}
