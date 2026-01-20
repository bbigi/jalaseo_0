<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeepResearchCompetitor extends Model
{
    protected $table = 'ane_deep_research_competitors';
    
    public $timestamps = false;
    
    protected $fillable = [
        'deep_research_id', 'competitor_domain', 'competitor_type',
        'appears_in_keywords', 'avg_rank', 'keyword_count', 'keywords_profiled',
        'keywords_data', 'keyword_gap', 'top_pages', 'top_keywords',
        'traffic_estimate', 'featured_snippets', 'gap_score'
    ];
    
    protected $casts = [
        'appears_in_keywords' => 'integer',
        'avg_rank' => 'decimal:2',
        'keyword_count' => 'integer',
        'keywords_profiled' => 'boolean',
        'keywords_data' => 'array',
        'keyword_gap' => 'array',
        'top_pages' => 'array',
        'top_keywords' => 'array',
        'featured_snippets' => 'array',
        'gap_score' => 'decimal:2',
        'traffic_estimate' => 'integer',
        'created_at' => 'datetime',
    ];
    
    public function session(): BelongsTo
    {
        return $this->belongsTo(DeepResearchSession::class, 'deep_research_id');
    }
    
    // Scopes
    public function scopeDirect($query)
    {
        return $query->where('competitor_type', 'direct');
    }
    
    public function scopeTopRanking($query, int $limit = 10)
    {
        return $query->orderBy('avg_rank', 'asc')->limit($limit);
    }
}
