<?php

namespace Database\Seeders;

use App\Models\Internship;
use App\Models\Service;
use Illuminate\Database\Seeder;

class InternshipSeeder extends Seeder
{
    public function run(): void
    {
        $services = Service::all();

        // 1 active internship per service (up to 4 services)
        $services->take(4)->each(function (Service $service) {
            Internship::factory()->active()->create([
                'service_id' => $service->id,
            ]);
        });

        // 3 completed internships
        Internship::factory(3)->completed()->create([
            'service_id' => fn () => $services->random()->id,
        ]);

        // 2 upcoming internships
        Internship::factory(2)->create([
            'service_id' => fn () => $services->random()->id,
            'status' => 'upcoming',
            'start_date' => fn () => now()->addWeeks(fake()->numberBetween(2, 8))->format('Y-m-d'),
            'end_date' => fn () => now()->addWeeks(fake()->numberBetween(14, 20))->format('Y-m-d'),
        ]);
    }
}
