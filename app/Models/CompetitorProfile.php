<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne as EloquentHasOne;

class CompetitorProfile extends Model
{
    protected $table = 'ane_competitor_profiles';

    protected $fillable = [
        'website_id',
        'created_by',
        'domain',
        'added_by',
        'country',
        'language',
        'total_keywords',
        'avg_rank',
        'traffic_estimate',
        'market_share',
        'intent_distribution',
        'keyword_type_distribution',
        'content_format_distribution',
        'top_keywords',
        'top_urls',
        'ai_insights',
        'comparison_data',
        'comparison_generated_at',
        'comparison_status',
        'status',
        'is_primary',
        'last_analyzed_at',
        'notes',
    ];

    protected $casts = [
        'intent_distribution' => 'array',
        'keyword_type_distribution' => 'array',
        'content_format_distribution' => 'array',
        'top_keywords' => 'array',
        'top_urls' => 'array',
        'ai_insights' => 'array',
        'comparison_data' => 'array',
        'comparison_generated_at' => 'datetime',
        'last_analyzed_at' => 'datetime',
        'is_primary' => 'boolean',
    ];

    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class, 'website_id');
    }


    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(CompetitorAnalysisSession::class, 'competitor_id');
    }

    public function keywords(): HasMany
    {
        return $this->hasMany(CompetitorKeyword::class, 'competitor_id');
    }

    public function rawResponses(): HasMany
    {
        return $this->hasMany(CompetitorRaw::class, 'competitor_id');
    }

    public function latestAdvancedSession(): EloquentHasOne
    {
        return $this->hasOne(CompetitorAdvancedSession::class, 'competitor_id')
            ->latest('finished_at')
            ->latest('updated_at');
    }
}
