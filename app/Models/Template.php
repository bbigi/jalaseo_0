<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Template extends Model
{
    protected $table = 'ane_templates';

    protected $fillable = [
        'name',
        'category_id',
        'template_type',
        'canvas_json',
        'thumbnail_path',
        'canvas_width',
        'canvas_height',
        'is_public',
        'is_default',
        'created_by',
    ];

    protected $casts = [
        'canvas_json' => 'array',
        'is_public' => 'boolean',
        'is_default' => 'boolean',
        'canvas_width' => 'integer',
        'canvas_height' => 'integer',
    ];

    /**
     * Relationship: Template belongs to User (creator)
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relationship: Template belongs to Category
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(TemplateCategory::class, 'category_id');
    }

    /**
     * Relationship: Template has many brandings using it
     */
    public function brandings(): HasMany
    {
        return $this->hasMany(UserBranding::class, 'default_template_id');
    }

    /**
     * Scope: Public templates (accessible by all users)
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', 1);
    }

    /**
     * Scope: User templates (created by specific user)
     */
    public function scopeUserTemplates($query, $userId)
    {
        return $query->where('created_by', $userId);
    }

    /**
     * Scope: Accessible templates (public OR user-owned)
     */
    public function scopeAccessible($query, $userId)
    {
        return $query->where(function($q) use ($userId) {
            $q->where('is_public', 1)
              ->orWhere('created_by', $userId);
        });
    }

    /**
     * Scope: Default templates by category
     */
    public function scopeDefault($query, $categoryId = null)
    {
        $query = $query->where('is_default', 1);

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        return $query;
    }

    /**
     * Scope: Article templates only
     */
    public function scopeArticleTemplates($query)
    {
        return $query->where('template_type', 'article');
    }

    /**
     * Scope: Standalone templates only
     */
    public function scopeStandaloneTemplates($query)
    {
        return $query->where('template_type', 'standalone');
    }

    /**
     * Helper: Get thumbnail URL
     */
    public function getThumbnailUrlAttribute()
    {
        if ($this->thumbnail_path) {
            return asset('storage/' . $this->thumbnail_path);
        }

        return asset('images/template-placeholder.png');
    }
}
