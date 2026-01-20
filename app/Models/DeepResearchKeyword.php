<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeepResearchKeyword extends Model
{
    protected $table = 'ane_deep_research_keywords';
    
    public $timestamps = false;
    
    protected $fillable = [
        'deep_research_id', 'keyword', 'keyword_hash', 'source',
        'volume', 'competition_level', 'competition_index', 'cpc',
        'difficulty_score', 'opportunity_score', 'ai_priority_score',
        'intent', 'ai_recommended_action',
        'save_status', 'save_reason', 'from_gap'
    ];
    
    protected $casts = [
        'volume' => 'integer',
        'competition_index' => 'integer',
        'cpc' => 'decimal:2',
        'difficulty_score' => 'integer',
        'opportunity_score' => 'integer',
        'ai_priority_score' => 'integer',
        'from_gap' => 'boolean',
        'created_at' => 'datetime',
    ];
    
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (!$model->keyword_hash && $model->keyword) {
                $model->keyword_hash = md5(strtolower(trim($model->keyword)));
            }
        });
    }
    
    public function session(): BelongsTo
    {
        return $this->belongsTo(DeepResearchSession::class, 'deep_research_id');
    }
    
    // Scopes
    public function scopeHighPriority($query)
    {
        return $query->where('ai_priority_score', '>=', 70);
    }
    
    public function scopeSaved($query)
    {
        return $query->whereIn('save_status', ['user_saved', 'auto_saved']);
    }
    
    public function scopeBySource($query, string $source)
    {
        return $query->where('source', $source);
    }
}
