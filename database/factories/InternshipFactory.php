<?php

namespace Database\Factories;

use App\Models\Internship;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Internship>
 */
class InternshipFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('-6 months', '+2 months');
        $endDate = (clone $startDate)->modify('+'.fake()->numberBetween(6, 12).' weeks');

        $batchYear = date('Y', $startDate->getTimestamp());
        $batchLetter = fake()->randomElement(['A', 'B', 'C']);
        $batchNumber = fake()->numberBetween(1, 10);

        return [
            'service_id' => fn() => Service::inRandomOrder()->first()->id ?? Service::factory(),
            'name' => fake()->randomElement([
                'Front-End Development Bootcamp',
                'Full-Stack Web Development Program',
                'WordPress Development Training',
                'UI/UX Design Internship',
                'SEO & Digital Marketing Program',
                'Graphic Designing Bootcamp',
                'AI Automation Workshop',
                'Shopify Development Course',
            ]),
            'batch_no' => "BATCH-{$batchNumber}-{$batchYear}{$batchLetter}",
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'status' => fake()->randomElement(['upcoming', 'active', 'completed']),
        ];
    }

    /** Internship is currently active. */
    public function active(): static
    {
        $startDate = now()->subWeeks(4);
        $endDate = now()->addWeeks(8);

        return $this->state([
            'status' => 'active',
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
        ]);
    }

    /** Internship is completed. */
    public function completed(): static
    {
        $startDate = fake()->dateTimeBetween('-1 year', '-3 months');
        $endDate = (clone $startDate)->modify('+3 months');

        return $this->state([
            'status' => 'completed',
            'start_date' => date('Y-m-d', $startDate->getTimestamp()),
            'end_date' => date('Y-m-d', $endDate->getTimestamp()),
        ]);
    }
}
