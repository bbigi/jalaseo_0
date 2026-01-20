<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TemplateCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Social Media', 'slug' => 'social-media', 'icon' => 'share', 'sort_order' => 1],
            ['name' => 'Marketing', 'slug' => 'marketing', 'icon' => 'megaphone', 'sort_order' => 2],
            ['name' => 'Business', 'slug' => 'business', 'icon' => 'briefcase', 'sort_order' => 3],
            ['name' => 'Events', 'slug' => 'events', 'icon' => 'calendar', 'sort_order' => 4],
            ['name' => 'Education', 'slug' => 'education', 'icon' => 'book', 'sort_order' => 5],
            ['name' => 'Tech', 'slug' => 'tech', 'icon' => 'cpu', 'sort_order' => 6],
        ];

        foreach ($categories as $category) {
            \App\Models\TemplateCategory::firstOrCreate(
                ['slug' => $category['slug']],
                [
                    'name' => $category['name'],
                    'icon' => $category['icon'],
                    'sort_order' => $category['sort_order'],
                    'is_active' => true,
                ]
            );
        }
    }
}
