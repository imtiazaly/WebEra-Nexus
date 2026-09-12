<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Project;
use App\Models\Service;
use App\Models\Student;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        $services = Service::all();
        $convertedClients = Client::where('status', 'converted')->get();
        $students = Student::all();

        // 6 client projects (linked to converted clients)
        $convertedClients->each(function (Client $client) use ($services) {
            $count = fake()->numberBetween(1, 2);
            Project::factory($count)->create([
                'client_id' => $client->id,
                'service_id' => $services->random()->id,
            ]);
        });

        // 4 internal practice projects (no client)
        Project::factory(4)->internal()->create([
            'service_id' => fn () => $services->random()->id,
        ]);

        // Assign students to projects via pivot
        if ($students->isNotEmpty()) {
            Project::all()->each(function (Project $project) use ($students) {
                $assignedStudents = $students->random(min(fake()->numberBetween(1, 3), $students->count()));
                $pivotData = $assignedStudents->mapWithKeys(fn (Student $student) => [
                    $student->id => [
                        'role' => fake()->randomElement([
                            'Intern Developer',
                            'Frontend Developer',
                            'Backend Developer',
                            'UI Designer',
                            'SEO Specialist',
                            'Full-Stack Developer',
                        ]),
                        'progress' => fake()->numberBetween(0, 100),
                        'submission_status' => fake()->randomElement([
                            'assigned', 'in_progress', 'submitted', 'reviewed', 'approved', 'revision_needed',
                        ]),
                    ],
                ])->all();

                $project->students()->attach($pivotData);
            });
        }
    }
}
