<?php

namespace Database\Seeders;

use App\Models\Internship;
use App\Models\Student;
use App\Models\WeeklyReport;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $internships = Internship::all();

        $internships->each(function (Internship $internship) {
            $studentCount = match ($internship->status) {
                'active' => fake()->numberBetween(4, 8),
                'completed' => fake()->numberBetween(3, 6),
                'upcoming' => fake()->numberBetween(1, 3),
                default => 2,
            };

            $studentStatus = match ($internship->status) {
                'active' => 'active',
                'completed' => 'completed',
                'upcoming' => 'enrolled',
                default => 'enrolled',
            };

            Student::factory($studentCount)
                ->create([
                    'internship_id' => $internship->id,
                    'status' => $studentStatus,
                    'overall_progress' => match ($studentStatus) {
                        'active' => fn () => fake()->numberBetween(20, 80),
                        'completed' => fn () => fake()->numberBetween(80, 100),
                        default => fn () => fake()->numberBetween(0, 15),
                    },
                ])
                ->each(function (Student $student) use ($internship) {
                    // Only active/completed students get weekly reports
                    if (in_array($internship->status, ['active', 'completed'])) {
                        $weeksCount = $internship->status === 'completed'
                            ? fake()->numberBetween(6, 10)
                            : fake()->numberBetween(1, 4);

                        collect(range(1, $weeksCount))->each(function (int $week) use ($student) {
                            WeeklyReport::factory()->create([
                                'student_id' => $student->id,
                                'week_number' => $week,
                                'status' => $week < fake()->numberBetween(2, 4)
                                    ? 'approved'
                                    : fake()->randomElement(['submitted', 'under_review', 'approved']),
                            ]);
                        });
                    }
                });
        });
    }
}
