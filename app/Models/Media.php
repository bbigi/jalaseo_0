<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    use HasFactory;

    protected $table = 'ane_media';

    public $timestamps = false; // Only created_at in schema

    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    protected $fillable = [
        'post_id',
        'website_id',
        'user_id',
        'file_name',
        'file_path',
        'file_type',
        'mime_type',
        'file_size',
        'title',
        'alt_text',
        'description',
        'caption',
        'source_type',
        'ai_prompt',
        'is_featured',
        'wp_media_id',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'file_size' => 'integer',
        'wp_media_id' => 'integer',
        'created_at' => 'datetime',
    ];

    protected $appends = ['full_url'];

    // Relationships
    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id');
    }

    public function website()
    {
        return $this->belongsTo(Website::class, 'website_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function posts()
    {
        return $this->belongsToMany(Post::class, 'ane_post_media', 'media_id', 'post_id')
            ->withPivot('is_featured');
    }

    // Accessors
    public function getFullUrlAttribute()
    {
        // If source is Unsplash or file_path is already a full URL, return it directly
        if ($this->source_type === 'unsplash' || str_starts_with($this->file_path, 'http')) {
            return $this->file_path;
        }

        return asset('storage/' . $this->file_path);
    }
}
