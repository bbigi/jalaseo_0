<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompetitorKeyword extends Model
{
    protected $table = 'ane_competitor_keywords';

    public $timestamps = false;

    protected $fillable = [
        'competitor_id',
        'session_id',
        'keyword',
        'position',
        'search_volume',
        'cpc',
        'competition',
        'traffic_share',
        'url',
        'intent',
        'keyword_type',
        'content_format',
        'source',
    ];

    public function competitor(): BelongsTo
    {
        return $this->belongsTo(CompetitorProfile::class, 'competitor_id');
    }
}
