<?php

namespace Database\Factories;

use App\Models\Student;
use App\Models\WeeklyReport;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WeeklyReport>
 */
class WeeklyReportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = fake()->randomElement(['submitted', 'under_review', 'approved', 'revision_requested']);

        return [
            'student_id' => Student::factory(),
            'week_number' => fake()->numberBetween(1, 12),
            'tasks_completed' => fake()->paragraph(),
            'learnings' => fake()->optional(0.8)->paragraph(),
            'blockers' => fake()->optional(0.4)->sentence(),
            'status' => $status,
            'feedback' => in_array($status, ['approved', 'revision_requested'])
                ? fake()->sentence()
                : null,
            'ai_summary' => null,
            'submitted_at' => fake()->dateTimeBetween('-8 weeks', 'now'),
        ];
    }

    /** Report is approved. */
    public function approved(): static
    {
        return $this->state([
            'status' => 'approved',
            'feedback' => fake()->sentence(),
        ]);
    }
}
