<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Service;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        $services = Service::all();

        // 5 converted clients (active, with services attached)
        Client::factory(5)
            ->converted()
            ->create()
            ->each(function (Client $client) use ($services) {
                $randomServices = $services->random(fake()->numberBetween(1, 3));
                $pivotData = $randomServices->mapWithKeys(fn (Service $service) => [
                    $service->id => [
                        'requirements' => fake()->optional(0.7)->paragraph(),
                        'estimated_budget' => fake()->optional(0.8)->randomFloat(2, 500, 10000),
                    ],
                ])->all();

                $client->services()->attach($pivotData);
            });

        // 3 new leads
        Client::factory(3)->newLead()->create();

        // 2 contacted
        Client::factory(2)->create(['status' => 'contacted']);

        // 1 lost
        Client::factory(1)->create(['status' => 'lost']);
    }
}
