<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class DeepResearchSession extends Model
{
    protected $table = 'ane_deep_research_sessions';
    
    protected $fillable = [
        'session_id', 'website_id', 'created_by', 'seed_keyword', 'seed_keyword_hash',
        'location', 'language',
        'status', 'progress', 'current_step', 'error_message',
        'autocomplete_results', 'selected_keywords', 'serp_snapshot', 
        'paa_data', 'related_searches', 'competitors',
        'ai_analysis', 'ai_recommendations', 'content_blueprint',
        'keywords_count', 'total_competitors_found', 'auto_saved_keywords_count',
        'total_api_cost', 'started_at', 'completed_at'
    ];
    
    protected $casts = [
        'autocomplete_results' => 'array',
        'selected_keywords' => 'array',
        'serp_snapshot' => 'array',
        'paa_data' => 'array',
        'related_searches' => 'array',
        'competitors' => 'array',
        'ai_analysis' => 'array',
        'ai_recommendations' => 'array',
        'content_blueprint' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'total_api_cost' => 'decimal:4',
    ];
    
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (!$model->session_id) {
                $model->session_id = Str::uuid()->toString();
            }
            if (!$model->seed_keyword_hash && $model->seed_keyword) {
                $model->seed_keyword_hash = md5(strtolower(trim($model->seed_keyword)));
            }
        });
    }
    
    // Relationships
    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class, 'website_id');
    }
    
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    public function keywords(): HasMany
    {
        return $this->hasMany(DeepResearchKeyword::class, 'deep_research_id');
    }
    
    public function competitorAnalysis(): HasMany
    {
        return $this->hasMany(DeepResearchCompetitor::class, 'deep_research_id');
    }
    
    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
    
    public function scopeForWebsite($query, $websiteId)
    {
        return $query->where('website_id', $websiteId);
    }

    /**
     * Limit sessions to those visible to the given user (unless super admin).
     */
    public function scopeVisibleTo($query, $user)
    {
        if (!$user || ($user->role ?? 'user') !== 'super_admin') {
            $query->where('created_by', $user?->id ?? 0);
        }

        return $query;
    }
    
    // Helpers
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }
    
    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }
    
    public function getProcessingDuration(): ?int
    {
        if (!$this->started_at) return null;
        $endTime = $this->completed_at ?? now();
        return $this->started_at->diffInSeconds($endTime);
    }
}
