<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContentDNA extends Model
{
    protected $table = 'ane_content_dna';

    protected $fillable = [
        'post_id',
        'created_by',
        'headline',
        'subheadline',
        'hot_take',
        'cta',
        'key_points',
        'keywords',
        'summary_short',
        'summary_medium',
        'summary_long',
        'tone',
        'intent',
    ];

    protected $casts = [
        'key_points' => 'array',
        'keywords' => 'array',
    ];

    /**
     * Get the post that owns the DNA
     */
    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id');
    }

    /**
     * Get the user who created this DNA
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
