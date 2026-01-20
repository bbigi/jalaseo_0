<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NewsResult extends Model
{
    protected $table = 'ane_news_results';

    protected $fillable = [
        'search_id',
        'title',
        'url',
        'snippet',
        'publish_date',
        'position',
    ];

    protected $casts = [
        'publish_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Parent search
     */
    public function search(): BelongsTo
    {
        return $this->belongsTo(NewsSearch::class, 'search_id');
    }
}
