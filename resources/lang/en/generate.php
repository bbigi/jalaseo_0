<?php

return [
    'title' => 'AI Article Generator',
    'subtitle' => 'Generate high-quality articles with AI assistance',
    'form' => [
        'website' => 'Target Website',
        'website_optional' => '(optional)',
        'website_placeholder' => 'Select website...',
        'website_search' => 'Search website...',

        'template' => 'Template',
        'template_placeholder' => 'Select template...',
        'template_search' => 'Search template...',
        'template_create' => '+ Create new template',

        'generation_source' => 'Generation Source',
        'generation_source_placeholder' => 'Generate from Title',
        'generation_source_search' => 'Search source...',
        'generation_options' => [
            'title' => 'Generate from Title',
            'reference' => 'Generate from Reference (URL/Description)',
            'text' => 'Generate from Text (Manual Input)',
            'insight' => 'Generate from Insight',
        ],

        'topic' => 'Topic/Keywords',
        'topic_placeholder' => 'Enter topic or keyword',
        'reference_button' => 'Find Reference',
        'autocomplete_help' => 'Autocomplete combines saved keywords and live Google suggestions.',

        'reference_topic' => 'Reference Topic',
        'reference_topic_placeholder' => 'Main topic for reference',
        'reference_description' => 'Reference Description',
        'reference_description_placeholder' => 'Brief description or key points',
        'reference_url' => 'Reference URL (optional)',
        'reference_url_placeholder' => 'https://example.com/article',

        'insight_select' => 'Select Insight',
        'insight_placeholder' => 'Select insight...',
        'insight_search' => 'Search insight...',
        'insight_empty' => 'No insights available',
        'insight_help' => 'Insight topic and description will be used as references.',

        'text_reference' => 'Reference Text',
        'text_reference_placeholder' => 'Paste the text you want AI to rewrite or expand...',
        'text_source_name' => 'Source Name (optional)',
        'text_source_name_placeholder' => 'e.g., Wikipedia, Research Paper',

        'submit' => 'Generate Article',
    ],
];
