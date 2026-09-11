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
            [
                'name' => 'WordPress Development',
                'description' => 'Custom WordPress theme & plugin development, WooCommerce setups, and optimizations.',
            ],
            [
                'name' => 'Full-Stack Development',
                'description' => 'Custom web application development using Laravel, Inertia, React/Vue, and modern APIs.',
            ],
            [
                'name' => 'Shopify Development',
                'description' => 'Custom Shopify store setup, Liquid theme customization, and app integrations.',
            ],
            [
                'name' => 'UI/UX Design',
                'description' => 'User interface design, wireframing, interactive prototyping, and design systems.',
            ],
            [
                'name' => 'SEO Services',
                'description' => 'On-page SEO, technical audits, content strategy, and search engine optimization.',
            ],
            [
                'name' => 'Graphic Designing',
                'description' => 'Brand identity, marketing collateral, social media design, and vector assets.',
            ],
            [
                'name' => 'AI Automation',
                'description' => 'AI workflows, custom LLM integrations, chatbot development, and business automations.',
            ],
        ];

        foreach ($services as $service) {
            Service::updateOrCreate(
                ['slug' => Str::slug($service['name'])],
                [
                    'name' => $service['name'],
                    'description' => $service['description'],
                    'is_active' => true,
                ]
            );
        }
    }
}
