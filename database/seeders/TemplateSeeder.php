<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Template;

class TemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // News Template - Simple text with branding
        Template::create([
            'name' => 'News Article Default',
            'category' => 'news',
            'canvas_width' => 1080,
            'canvas_height' => 1080,
            'is_public' => true,
            'is_default' => true,
            'created_by' => null,
            'canvas_json' => [
                'attrs' => [
                    'width' => 1080,
                    'height' => 1080,
                ],
                'className' => 'Stage',
                'children' => [
                    [
                        'attrs' => [],
                        'className' => 'Layer',
                        'children' => [
                            // Background
                            [
                                'attrs' => [
                                    'x' => 0,
                                    'y' => 0,
                                    'width' => 1080,
                                    'height' => 1080,
                                    'fill' => '{brand_color_primary}',
                                ],
                                'className' => 'Rect',
                            ],
                            // Logo (top center)
                            [
                                'attrs' => [
                                    'x' => 440,
                                    'y' => 100,
                                    'width' => 200,
                                    'height' => 200,
                                    'image' => '{brand_logo}',
                                ],
                                'className' => 'Image',
                            ],
                            // Article Title (centered)
                            [
                                'attrs' => [
                                    'x' => 80,
                                    'y' => 400,
                                    'width' => 920,
                                    'text' => '{article_title}',
                                    'fontSize' => 48,
                                    'fontFamily' => 'Inter',
                                    'fontStyle' => 'bold',
                                    'fill' => '{brand_color_secondary}',
                                    'align' => 'center',
                                    'wrap' => 'word',
                                ],
                                'className' => 'Text',
                            ],
                            // Website URL (bottom)
                            [
                                'attrs' => [
                                    'x' => 80,
                                    'y' => 920,
                                    'width' => 920,
                                    'text' => '{website_url}',
                                    'fontSize' => 24,
                                    'fontFamily' => 'Inter',
                                    'fill' => '{brand_color_secondary}',
                                    'align' => 'center',
                                    'opacity' => 0.8,
                                ],
                                'className' => 'Text',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        // Article Template - With excerpt and more details
        Template::create([
            'name' => 'Blog Article Default',
            'category' => 'article',
            'canvas_width' => 1080,
            'canvas_height' => 1080,
            'is_public' => true,
            'is_default' => true,
            'created_by' => null,
            'canvas_json' => [
                'attrs' => [
                    'width' => 1080,
                    'height' => 1080,
                ],
                'className' => 'Stage',
                'children' => [
                    [
                        'attrs' => [],
                        'className' => 'Layer',
                        'children' => [
                            // Background (gradient using 2 rectangles)
                            [
                                'attrs' => [
                                    'x' => 0,
                                    'y' => 0,
                                    'width' => 1080,
                                    'height' => 1080,
                                    'fill' => '{brand_color_primary}',
                                ],
                                'className' => 'Rect',
                            ],
                            // Top accent bar
                            [
                                'attrs' => [
                                    'x' => 0,
                                    'y' => 0,
                                    'width' => 1080,
                                    'height' => 20,
                                    'fill' => '{brand_color_secondary}',
                                ],
                                'className' => 'Rect',
                            ],
                            // Logo (top left)
                            [
                                'attrs' => [
                                    'x' => 60,
                                    'y' => 60,
                                    'width' => 120,
                                    'height' => 120,
                                    'image' => '{brand_logo}',
                                ],
                                'className' => 'Image',
                            ],
                            // Article Title
                            [
                                'attrs' => [
                                    'x' => 60,
                                    'y' => 250,
                                    'width' => 960,
                                    'text' => '{article_title}',
                                    'fontSize' => 52,
                                    'fontFamily' => 'Inter',
                                    'fontStyle' => 'bold',
                                    'fill' => '{brand_color_secondary}',
                                    'lineHeight' => 1.2,
                                    'wrap' => 'word',
                                ],
                                'className' => 'Text',
                            ],
                            // Article Excerpt
                            [
                                'attrs' => [
                                    'x' => 60,
                                    'y' => 550,
                                    'width' => 960,
                                    'text' => '{article_excerpt}',
                                    'fontSize' => 28,
                                    'fontFamily' => 'Inter',
                                    'fill' => '{brand_color_secondary}',
                                    'lineHeight' => 1.5,
                                    'wrap' => 'word',
                                    'opacity' => 0.9,
                                ],
                                'className' => 'Text',
                            ],
                            // Author name
                            [
                                'attrs' => [
                                    'x' => 60,
                                    'y' => 880,
                                    'text' => 'By {article_author}',
                                    'fontSize' => 22,
                                    'fontFamily' => 'Inter',
                                    'fill' => '{brand_color_secondary}',
                                    'opacity' => 0.7,
                                ],
                                'className' => 'Text',
                            ],
                            // Website URL (bottom right)
                            [
                                'attrs' => [
                                    'x' => 60,
                                    'y' => 990,
                                    'width' => 960,
                                    'text' => '{website_url}',
                                    'fontSize' => 20,
                                    'fontFamily' => 'Inter',
                                    'fill' => '{brand_color_secondary}',
                                    'align' => 'right',
                                    'opacity' => 0.6,
                                ],
                                'className' => 'Text',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        // Quote Template
        Template::create([
            'name' => 'Quote Post',
            'category' => 'quote',
            'canvas_width' => 1080,
            'canvas_height' => 1080,
            'is_public' => true,
            'is_default' => false,
            'created_by' => null,
            'canvas_json' => [
                'attrs' => [
                    'width' => 1080,
                    'height' => 1080,
                ],
                'className' => 'Stage',
                'children' => [
                    [
                        'attrs' => [],
                        'className' => 'Layer',
                        'children' => [
                            // Background
                            [
                                'attrs' => [
                                    'x' => 0,
                                    'y' => 0,
                                    'width' => 1080,
                                    'height' => 1080,
                                    'fill' => '{brand_color_secondary}',
                                ],
                                'className' => 'Rect',
                            ],
                            // Quote mark icon (large decorative)
                            [
                                'attrs' => [
                                    'x' => 120,
                                    'y' => 200,
                                    'text' => '"',
                                    'fontSize' => 200,
                                    'fontFamily' => 'Georgia',
                                    'fill' => '{brand_color_primary}',
                                    'opacity' => 0.3,
                                ],
                                'className' => 'Text',
                            ],
                            // Quote text
                            [
                                'attrs' => [
                                    'x' => 120,
                                    'y' => 400,
                                    'width' => 840,
                                    'text' => '{article_title}',
                                    'fontSize' => 42,
                                    'fontFamily' => 'Georgia',
                                    'fontStyle' => 'italic',
                                    'fill' => '{brand_color_primary}',
                                    'lineHeight' => 1.4,
                                    'wrap' => 'word',
                                ],
                                'className' => 'Text',
                            ],
                            // Author attribution
                            [
                                'attrs' => [
                                    'x' => 120,
                                    'y' => 780,
                                    'text' => '— {article_author}',
                                    'fontSize' => 28,
                                    'fontFamily' => 'Inter',
                                    'fill' => '{brand_color_primary}',
                                    'opacity' => 0.8,
                                ],
                                'className' => 'Text',
                            ],
                            // Logo (bottom right corner)
                            [
                                'attrs' => [
                                    'x' => 880,
                                    'y' => 880,
                                    'width' => 100,
                                    'height' => 100,
                                    'image' => '{brand_logo}',
                                    'opacity' => 0.8,
                                ],
                                'className' => 'Image',
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }
}
