<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $table = 'ane_posts';

    protected $fillable = [
        'insight_id',
        'content_strategy_id',
        'article_role',
        'source_topic_id',
        'title',
        'article_type',
        'content',
        'seo_focus_keyword',
        'seo_meta_description',
        'ai_generated_tags',
        'featured_media_id',
        'source_type',
        'created_by',
    ];

    protected $casts = [
        'article_type' => 'string',
        'source_type' => 'string',
        'ai_generated_tags' => 'array',
    ];

    // Relationships
    public function owner()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function insight()
    {
        return $this->belongsTo(Insight::class, 'insight_id');
    }

    public function insights()
    {
        return $this->belongsToMany(Insight::class, 'ane_insight_articles', 'post_id', 'insight_id')
            ->withPivot('website_id', 'created_by')
            ->as('article');
    }

    public function media()
    {
        return $this->belongsToMany(Media::class, 'ane_post_media', 'post_id', 'media_id')
            ->withPivot('is_featured');
    }

    public function targets()
    {
        return $this->hasMany(PostTarget::class, 'post_id');
    }

    public function featuredMedia()
    {
        return $this->belongsTo(Media::class, 'featured_media_id');
    }

    /**
     * Get the content DNA for this post
     */
    public function contentDNA()
    {
        return $this->hasOne(ContentDNA::class, 'post_id');
    }

    /**
     * Get all social assets for this post
     */
    public function socialAssets()
    {
        return $this->hasMany(SocialAsset::class, 'post_id');
    }

    /**
     * Get social assets by channel
     */
    public function socialAssetsByChannel(string $channel)
    {
        return $this->socialAssets()->where('channel', $channel)->get();
    }
}
