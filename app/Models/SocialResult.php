<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocialResult extends Model
{
    protected $table = 'ane_social_results';

    const UPDATED_AT = null; // No updated_at column

    protected $fillable = [
        'search_id',
        'platform',
        'content',
        'author',
        'link',
        'engagement_likes',
        'engagement_comments',
        'engagement_shares',
        'sentiment',
        'hashtags',
        'is_trending',
    ];

    protected $casts = [
        'hashtags' => 'array',
        'is_trending' => 'boolean',
    ];

    public function search()
    {
        return $this->belongsTo(SocialSearch::class, 'search_id');
    }
}
