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
        // Admin User
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@webera.com',
        ]);

        $this->call([
            ServiceSeeder::class,
            SettingSeeder::class,
            ClientSeeder::class,
            InternshipSeeder::class,
            StudentSeeder::class,
            ProjectSeeder::class,
        ]);
    }
}
