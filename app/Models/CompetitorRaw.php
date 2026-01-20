<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompetitorRaw extends Model
{
    protected $table = 'ane_competitor_raw';

    public $timestamps = false;

    protected $fillable = [
        'competitor_id',
        'session_id',
        'source',
        'payload',
        'api_cost',
        'response_time_ms',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    public function competitor(): BelongsTo
    {
        return $this->belongsTo(CompetitorProfile::class, 'competitor_id');
    }
}
