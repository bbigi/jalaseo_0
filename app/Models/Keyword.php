<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Keyword extends Model
{
    protected $table = 'ane_keywords';

    protected $fillable = [
        'website_id',
        'keyword',
        'source',
        'volume',
        'difficulty',
        'status',
        'priority',
        'tracking_status',
        'target_position',
        'current_position',
        'has_deep_research_data',
        'competition_index',
        'intent',
        'first_tracked_at',
        'last_synced_at',
    ];

    protected $casts = [
        'has_deep_research_data' => 'boolean',
        'first_tracked_at' => 'datetime',
        'last_synced_at' => 'datetime',
    ];

    // Relationships
    public function website()
    {
        return $this->belongsTo(Website::class, 'website_id');
    }

    // Scopes
    public function scopeByUserWebsites($query)
    {
        return $query->whereHas('website', function($q) {
            $q->where('created_by', auth()->id());
        });
    }

    public function scopeHighPriority($query)
    {
        return $query->where('priority', 'high');
    }

    public function scopeActiveTracking($query)
    {
        return $query->where('tracking_status', 'active');
    }

    public function scopeFromSource($query, string $source)
    {
        return $query->where('source', $source);
    }

    public function scopeInGoogle($query)
    {
        return $query->whereNotNull('current_position');
    }

    // Accessors
    public function getRankingChangeAttribute()
    {
        if (!$this->current_position || !$this->target_position) {
            return null;
        }
        return $this->target_position - $this->current_position;
    }

    public function getIsOnTargetAttribute()
    {
        if (!$this->current_position || !$this->target_position) {
            return false;
        }
        return $this->current_position <= $this->target_position;
    }
}
