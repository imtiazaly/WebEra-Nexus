<?php

namespace Database\Factories;

use App\Models\Internship;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Student>
 */
class StudentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'internship_id' => Internship::factory(),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->optional(0.75)->numerify('+92-3##-#######'),
            'status' => fake()->randomElement(['enrolled', 'active', 'completed', 'dropped_out']),
            'overall_progress' => fake()->numberBetween(0, 100),
        ];
    }

    /** Student is currently active. */
    public function active(): static
    {
        return $this->state([
            'status' => 'active',
            'overall_progress' => fake()->numberBetween(20, 80),
        ]);
    }

    /** Student has completed the internship. */
    public function completed(): static
    {
        return $this->state([
            'status' => 'completed',
            'overall_progress' => fake()->numberBetween(80, 100),
        ]);
    }
}
