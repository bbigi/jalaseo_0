<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompetitorAdvancedSession extends Model
{
    protected $table = 'ane_competitor_advanced_sessions';

    protected $fillable = [
        'competitor_id',
        'user_id',
        'status',
        'progress',
        'error_message',
        'summary_metrics',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'summary_metrics' => 'array',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function competitor(): BelongsTo
    {
        return $this->belongsTo(CompetitorProfile::class, 'competitor_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
