<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompetitorRankedKeyword extends Model
{
    protected $table = 'ane_competitor_ranked_keywords';

    protected $fillable = [
        'competitor_id',
        'session_id',
        'keyword',
        'search_volume',
        'cpc',
        'competition_level',
        'rank_group',
        'rank_absolute',
        'url',
        'etv',
        'is_new',
        'title',
        'source',
    ];

    protected $casts = [
        'is_new' => 'boolean',
    ];

    public function competitor(): BelongsTo
    {
        return $this->belongsTo(CompetitorProfile::class, 'competitor_id');
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(CompetitorAdvancedSession::class, 'session_id');
    }
}
