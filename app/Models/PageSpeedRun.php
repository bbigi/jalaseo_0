<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageSpeedRun extends Model
{
    protected $table = 'ane_pagespeed_runs';

    protected $fillable = [
        'website_id',
        'url',
        'strategy',
        'status',
        'progress',
        'error_message',
        'lighthouse_version',
        'fetch_time',
        'scores',
        'lab_metrics',
        'field_metrics',
        'opportunities',
        'diagnostics',
        'resources_summary',
        'summary_text',
        'insights',
    ];

    protected $casts = [
        'scores' => 'array',
        'lab_metrics' => 'array',
        'field_metrics' => 'array',
        'opportunities' => 'array',
        'diagnostics' => 'array',
        'resources_summary' => 'array',
        'insights' => 'array',
        'fetch_time' => 'datetime',
    ];

    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class, 'website_id');
    }
}
