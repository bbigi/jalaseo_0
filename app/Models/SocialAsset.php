<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocialAsset extends Model
{
    protected $table = 'ane_social_assets';

    protected $fillable = [
        'post_id',
        'created_by',
        'channel',
        'asset_type',
        'copy_text',
        'copy_version',
        'visual_path',
        'visual_size',
        'visual_metadata',
        'canvas_json',
        'status',
    ];

    protected $casts = [
        'visual_metadata' => 'array',
        'canvas_json' => 'array',
    ];

    /**
     * Get the post that owns this asset
     */
    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id');
    }

    /**
     * Get the user who created this asset
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the distribution schedule for this asset
     */
    public function schedule()
    {
        return $this->hasOne(DistributionSchedule::class, 'asset_id');
    }

    /**
     * Get full URL for visual asset
     */
    public function getVisualUrlAttribute()
    {
        if (!$this->visual_path) {
            return null;
        }

        return asset('storage/' . $this->visual_path);
    }
}
