<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Placeholder accounts — replace with real names/emails/passwords before going live.
        User::create([
            'name' => 'Principal',
            'email' => 'principal@madrasa.test',
            'password' => 'password',
            'role' => 'principal',
        ]);

        for ($i = 1; $i <= 5; $i++) {
            User::create([
                'name' => "Teacher {$i}",
                'email' => "teacher{$i}@madrasa.test",
                'password' => 'password',
                'role' => 'teacher',
            ]);
        }
    }
}
