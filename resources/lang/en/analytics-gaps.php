<?php

return [
    'title' => 'Competitive Gap Analysis',
    'back' => 'Back to Website',
    'no_competitors' => [
        'title' => 'No competitors found',
        'description' => 'Add a competitor in Analytics to view gap analysis.',
    ],
    'controls' => [
        'select_competitor' => 'Select Competitor',
        'primary' => 'Primary',
        'search_label' => 'Search keyword',
        'search_placeholder' => 'Search keywords...',
        'filters' => [
            'all' => 'All',
            'we_win' => 'We Win',
            'opportunity' => 'Opportunities',
            'content_gap' => 'Content Gaps',
            'low_priority' => 'Low Priority',
        ],
    ],
    'stats' => [
        'total_keywords' => [
            'title' => 'Total Keywords',
            'subtitle' => 'Top 200 ranked keywords from competitor',
        ],
        'win_rate' => [
            'title' => 'Win Rate',
            'subtitle' => 'We rank higher than competitor',
        ],
        'median_delta' => [
            'title' => 'Median Delta',
            'subtitle' => 'Our rank minus competitor rank',
        ],
        'card_labels' => [
            'we_win' => 'We Win',
            'opportunity' => 'Opportunities',
            'content_gap' => 'Content Gaps',
            'low_priority' => 'Low Priority',
        ],
    ],
    'table' => [
        'keyword' => 'Keyword',
        'volume' => 'Volume',
        'our_position' => 'Our Position',
        'competitor_position' => 'Competitor Position',
        'delta' => 'Delta',
        'competitor_url' => 'Competitor URL',
        'etv' => 'ETV',
        'actions' => 'Actions',
        'badge' => [
            'ahead' => 'Ahead',
            'behind' => 'Behind',
            'same' => 'Same',
        ],
        'classification' => [
            'we_win' => 'We Win',
            'opportunity' => 'Opportunities',
            'content_gap' => 'Content Gaps',
            'low_priority' => 'Low Priority',
        ],
        'generate' => [
            'title' => 'Generate Article',
            'using_keyword' => 'Using Keyword',
            'duplicate' => 'Duplicate Content',
        ],
        'save_track' => 'Save and Track',
        'empty' => 'No keywords match this filter yet.',
    ],
    'pagination' => [
        'showing' => 'Showing :from to :to of :total keywords',
        'prev' => 'Previous',
        'next' => 'Next',
        'per_page' => 'Per page',
    ],
    'recommendations' => [
        'title' => 'AI Recommendations',
        'subtitle' => 'Generate a prioritized plan from your competitor gap data.',
        'button' => 'Generate Recommendations',
        'loading' => 'Generating recommendations...',
        'error' => 'Failed to generate recommendations. Please try again.',
        'empty' => 'No keywords available to analyze yet.',
        'last_generated' => 'Last generated:',
    ],
];
