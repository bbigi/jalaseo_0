<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostTarget extends Model
{
    use HasFactory;

    protected $table = 'ane_post_targets';

    protected $fillable = [
        'post_id',
        'website_id',
        'status',
        'scheduled_at',
        'published_at',
        'wp_post_id',
        'categories',
        'tags',
        'wp_response',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'published_at' => 'datetime',
        'wp_post_id' => 'integer',
        'categories' => 'array',
        'tags' => 'array',
    ];

    // Relationships
    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id');
    }

    public function website()
    {
        return $this->belongsTo(Website::class, 'website_id');
    }
}
