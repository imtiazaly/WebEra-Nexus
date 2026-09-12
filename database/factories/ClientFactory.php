<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Client>
 */
class ClientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->optional(0.8)->numerify('+92-3##-#######'),
            'company_name' => fake()->optional(0.7)->company(),
            'status' => fake()->randomElement(['new_lead', 'contacted', 'converted', 'lost']),
            'notes' => fake()->optional(0.6)->paragraph(),
            'ai_brief' => null,
        ];
    }

    /** Client is a fresh new lead. */
    public function newLead(): static
    {
        return $this->state(['status' => 'new_lead']);
    }

    /** Client has been converted (active client). */
    public function converted(): static
    {
        return $this->state(['status' => 'converted']);
    }
}
