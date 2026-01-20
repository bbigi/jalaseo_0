<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiGenerateTemplate extends Model
{
    use HasFactory;

    protected $table = 'ane_ai_generate_templates';

    protected $fillable = [
        'name',
        'ai_provider',
        'ai_model',
        'text_readability',
        'ai_content_cleaning',
        'description',
        'article_type',
        'article_size',
        'tone',
        'point_of_view',
        'target_country',
        'prompt_template',
        'language',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'ai_content_cleaning' => 'boolean',
    ];

    /**
     * Relationship: Template belongs to a user
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope: Active templates only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Filter by language
     */
    public function scopeLanguage($query, $language)
    {
        return $query->where('language', $language);
    }
}
