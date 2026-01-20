<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompetitorAnalysisSession extends Model
{
    protected $table = 'ane_competitor_analysis_sessions';

    protected $fillable = [
        'session_id',
        'competitor_id',
        'website_id',
        'created_by',
        'status',
        'progress',
        'current_step',
        'error_message',
        'raw_dataforseo_response',
        'analysis_summary',
    ];

    protected $casts = [
        'raw_dataforseo_response' => 'array',
        'analysis_summary' => 'array',
    ];

    public function competitor(): BelongsTo
    {
        return $this->belongsTo(CompetitorProfile::class, 'competitor_id');
    }

    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class, 'website_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
