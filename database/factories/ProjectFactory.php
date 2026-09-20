<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Project;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('-4 months', 'now');
        $deadline = (clone $startDate)->modify('+'.fake()->numberBetween(4, 16).' weeks');
        $status = fake()->randomElement(['planning', 'in_progress', 'on_hold', 'under_review', 'completed', 'cancelled']);

        return [
            'client_id' => fake()->boolean(80) ? Client::factory() : null,
            'service_id' => fn() => Service::inRandomOrder()->first()->id ?? Service::factory(),
            'title' => fake()->randomElement([
                'E-Commerce Website Redesign',
                'Corporate Portfolio Website',
                'WordPress Blog Setup',
                'Shopify Store Development',
                'Brand Identity Package',
                'SEO Audit & Optimization',
                'Custom CRM Dashboard',
                'Landing Page Design',
                'Mobile App UI Design',
                'Laravel API Backend',
            ]),
            'description' => fake()->optional(0.7)->paragraphs(2, true),
            'status' => $status,
            'start_date' => $startDate->format('Y-m-d'),
            'deadline' => $deadline->format('Y-m-d'),
            'progress' => $this->progressForStatus($status),
            'ai_status_summary' => null,
        ];
    }

    /** Internal practice project (no client). */
    public function internal(): static
    {
        return $this->state(['client_id' => null]);
    }

    /** Project is currently in progress. */
    public function inProgress(): static
    {
        return $this->state([
            'status' => 'in_progress',
            'progress' => fake()->numberBetween(20, 80),
        ]);
    }

    /** Project is completed. */
    public function completed(): static
    {
        return $this->state([
            'status' => 'completed',
            'progress' => 100,
        ]);
    }

    /** Derive a sensible progress % for a given status. */
    private function progressForStatus(string $status): int
    {
        return match ($status) {
            'planning' => fake()->numberBetween(0, 10),
            'in_progress' => fake()->numberBetween(15, 85),
            'on_hold' => fake()->numberBetween(10, 70),
            'under_review' => fake()->numberBetween(80, 95),
            'completed' => 100,
            'cancelled' => fake()->numberBetween(0, 60),
            default => 0,
        };
    }
}
