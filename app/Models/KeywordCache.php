<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KeywordCache extends Model
{
    protected $table = 'ane_keyword_cache';

    protected $fillable = [
        'keyword',
        'location',
        'language',
        'search_volume',
        'cpc',
        'competition',
        'competition_level',
        'trend_data',
        'related_keywords',
        'serp_features',
        'difficulty_score',
        'cached_at',
        'expires_at',
        'data_source',
    ];

    protected $casts = [
        'cpc' => 'float',
        'competition' => 'float',
        'trend_data' => 'array',
        'related_keywords' => 'array',
        'serp_features' => 'array',
        'cached_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    // Scopes
    public function scopeNotExpired($query)
    {
        return $query->where('expires_at', '>', now());
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }

    public function scopeForKeyword($query, $keyword, $location = 'ID', $language = 'id')
    {
        return $query->where('keyword', $keyword)
                     ->where('location', $location)
                     ->where('language', $language);
    }

    public function scopeHighVolume($query, $minVolume = 1000)
    {
        return $query->where('search_volume', '>=', $minVolume);
    }

    public function scopeLowDifficulty($query, $maxDifficulty = 30)
    {
        return $query->where('difficulty_score', '<=', $maxDifficulty);
    }

    public function scopeBySource($query, $source)
    {
        return $query->where('data_source', $source);
    }

    // Helpers
    public function isExpired()
    {
        return $this->expires_at <= now();
    }

    public function isValid()
    {
        return $this->expires_at > now();
    }
}
