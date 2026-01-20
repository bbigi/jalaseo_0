<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HealthSession extends Model
{
    protected $table = 'ane_health_sessions';

    protected $fillable = [
        'website_id',
        'status',
        'health_score',
        'indexing_score',
        'performance_score',
        'page_experience_score',
        'summary_json',
    ];

    protected $casts = [
        'health_score' => 'decimal:2',
        'indexing_score' => 'decimal:2',
        'performance_score' => 'decimal:2',
        'page_experience_score' => 'decimal:2',
        'summary_json' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class, 'website_id');
    }

    public function pageMetrics(): HasMany
    {
        return $this->hasMany(HealthPageMetric::class, 'session_id');
    }

    public function getComputedStatusAttribute(): string
    {
        if (!$this->health_score) {
            return 'Unknown';
        }

        if ($this->health_score >= 90) {
            return 'Excellent';
        }

        if ($this->health_score >= 75) {
            return 'Good';
        }

        if ($this->health_score >= 60) {
            return 'Needs Improvement';
        }

        return 'Critical';
    }

    public function getStatusColorAttribute(): string
    {
        if (!$this->health_score) {
            return 'gray';
        }

        if ($this->health_score >= 90) {
            return 'green';
        }

        if ($this->health_score >= 75) {
            return 'blue';
        }

        if ($this->health_score >= 60) {
            return 'yellow';
        }

        return 'red';
    }

    public function scopeLatestCompletedForWebsite($query, int $websiteId)
    {
        return $query->where('website_id', $websiteId)
            ->where('status', 'completed')
            ->latest()
            ->first();
    }
}
