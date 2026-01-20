<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NewsSearch extends Model
{
    protected $table = 'ane_news_searches';

    protected $fillable = [
        'created_by',
        'source',
        'theme',
        'time_range',
        'language',
        'country',
        'results_count',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * User who created the search
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Search results
     */
    public function results(): HasMany
    {
        return $this->hasMany(NewsResult::class, 'search_id');
    }
}
