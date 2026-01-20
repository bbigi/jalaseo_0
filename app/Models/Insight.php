<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Insight extends Model
{
    use HasFactory;

    protected $table = 'ane_insights';

    protected $fillable = [
        'topic',
        'description',
        'source',
        'source_url',
        'source_type',
        'status',
        'article_count',
        'created_by',
        'website_id',
    ];

    protected $casts = [
        'article_count' => 'integer',
    ];

    // Relationships
    public function owner()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function website()
    {
        return $this->belongsTo(Website::class, 'website_id');
    }

    public function posts()
    {
        return $this->belongsToMany(Post::class, 'ane_insight_articles', 'insight_id', 'post_id')
            ->withPivot('website_id', 'created_by')
            ->as('article')
            ->using(\Illuminate\Database\Eloquent\Relations\Pivot::class);
    }

    // Auto-update article count and status (replaces anewp database trigger)
    public function updateArticleCount(): void
    {
        $count = $this->posts()->count();
        $this->update([
            'article_count' => $count,
            'status' => $count > 0 ? 'in_progress' : 'pending',
        ]);
    }
}
