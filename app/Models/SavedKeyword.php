<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavedKeyword extends Model
{
    protected $table = 'ane_keywords_saved';

    protected $fillable = [
        'website_id',
        'keyword_text',
        'keyword_hash',
        'search_volume',
        'competition_level',
        'competition_index',
        'priority_score',
        'opportunity_score',
        'ai_priority_score',
        'intent',
        'keyword_type',
        'insights_count',
        'articles_count',
        'tracked_only',
        'save_reason',
        'current_position',
        'best_position',
        'is_tracking',
        'last_tracked_at',
        'saved_from',
        'deep_research_id',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'tracked_only' => 'boolean',
        'is_tracking' => 'boolean',
        'last_tracked_at' => 'datetime',
    ];

    // Relationships
    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    public function deepResearchSession(): BelongsTo
    {
        return $this->belongsTo(DeepResearchSession::class, 'deep_research_id');
    }

    // Helper: Generate MD5 hash
    public static function generateHash(string $keyword): string
    {
        return md5(strtolower(trim($keyword)));
    }

    // Helper: Save keyword from GSC
    public static function saveFromGSC(int $websiteId, string $keyword, float $position, int $userId): self
    {
        $hash = self::generateHash($keyword);

        return self::updateOrCreate(
            [
                'website_id' => $websiteId,
                'keyword_hash' => $hash,
            ],
            [
                'keyword_text' => $keyword,
                'current_position' => $position,
                'saved_from' => 'gsc',
                'created_by' => $userId,
            ]
        );
    }
}
