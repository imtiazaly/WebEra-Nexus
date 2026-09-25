<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            // Client Services
            [
                'name' => 'WordPress Client Development',
                'type' => 'client',
                'description' => 'Custom WordPress theme & plugin development, WooCommerce setups, and optimizations.',
            ],
            [
                'name' => 'Full-Stack Web Solutions',
                'type' => 'client',
                'description' => 'Custom web application development using Laravel, Inertia, React/Vue, and modern APIs.',
            ],
            [
                'name' => 'Shopify E-Commerce Store',
                'type' => 'client',
                'description' => 'Custom Shopify store setup, Liquid theme customization, and app integrations.',
            ],
            [
                'name' => 'SEO & Performance Audit',
                'type' => 'client',
                'description' => 'On-page SEO, technical audits, content strategy, and search engine optimization.',
            ],
            [
                'name' => 'Enterprise AI Automation',
                'type' => 'client',
                'description' => 'AI workflows, custom LLM integrations, chatbot development, and business automations.',
            ],

            // Internship Tracks
            [
                'name' => 'Full-Stack Web Development Track',
                'type' => 'internship',
                'description' => 'Hands-on training in Laravel, Inertia.js, Vue 3, and REST API development.',
            ],
            [
                'name' => 'UI/UX & Product Design Track',
                'type' => 'internship',
                'description' => 'User research, wireframing, interactive prototyping in Figma, and design systems.',
            ],
            [
                'name' => 'Python & AI Engineering Track',
                'type' => 'internship',
                'description' => 'Python backend engineering, machine learning basics, and LLM API integrations.',
            ],
            [
                'name' => 'Mobile App Development Track',
                'type' => 'internship',
                'description' => 'Cross-platform mobile app development with Flutter / React Native.',
            ],
        ];

        foreach ($services as $service) {
            Service::updateOrCreate(
                ['slug' => Str::slug($service['name'])],
                [
                    'name' => $service['name'],
                    'type' => $service['type'],
                    'description' => $service['description'],
                    'is_active' => true,
                ]
            );
        }
    }
}
