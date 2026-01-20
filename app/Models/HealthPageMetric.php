<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HealthPageMetric extends Model
{
    protected $table = 'ane_health_page_metrics';

    protected $fillable = [
        'session_id',
        'url',
        'performance_score',
        'lcp_ms',
        'inp_ms',
        'cls',
        'ttfb_ms',
        'issues_json',
    ];

    protected $casts = [
        'performance_score' => 'decimal:2',
        'lcp_ms' => 'integer',
        'inp_ms' => 'integer',
        'cls' => 'decimal:4',
        'ttfb_ms' => 'integer',
        'issues_json' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(HealthSession::class, 'session_id');
    }
}
