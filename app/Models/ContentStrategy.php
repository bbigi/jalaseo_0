<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContentStrategy extends Model
{
    protected $table = 'ane_content_strategies';

    protected $fillable = [
        'created_by',
        'search_id',
        'strategy_type',
        'selected_topics',
        'ranked_topics',
        'status',
        'expected_articles',
    ];

    protected $casts = [
        'selected_topics' => 'array',
        'ranked_topics' => 'array',
    ];

    /**
     * Relationship: Belongs to User (creator)
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relationship: Belongs to Social Search
     */
    public function search()
    {
        return $this->belongsTo(SocialSearch::class, 'search_id');
    }

    /**
     * Relationship: Has many Posts (generated articles)
     */
    public function posts()
    {
        return $this->hasMany(Post::class, 'content_strategy_id');
    }

    /**
     * Get selected trending results
     */
    public function selectedResults()
    {
        if (empty($this->selected_topics)) {
            return collect();
        }

        return SocialResult::whereIn('id', $this->selected_topics)
            ->orderByRaw('FIELD(id, ' . implode(',', $this->selected_topics) . ')')
            ->get();
    }

    /**
     * Get ranked results (after AI scoring)
     */
    public function rankedResults()
    {
        if (empty($this->ranked_topics)) {
            return collect();
        }

        // ranked_topics format: [{'id': 1, 'score': 94, 'tier': 1}, ...]
        $rankedData = collect($this->ranked_topics);
        $ids = $rankedData->pluck('id')->toArray();

        $results = SocialResult::whereIn('id', $ids)->get()->keyBy('id');

        return $rankedData->map(function ($item) use ($results) {
            $result = $results[$item['id']] ?? null;
            if ($result) {
                $result->ai_score = $item['score'] ?? null;
                $result->ai_tier = $item['tier'] ?? null;
                $result->ai_reasoning = $item['reasoning'] ?? null;
            }
            return $result;
        })->filter();
    }
}
