<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DistributionSchedule extends Model
{
    protected $table = 'ane_distribution_schedule';

    protected $fillable = [
        'asset_id',
        'created_by',
        'scheduled_at',
        'published_at',
        'platform_post_id',
        'publish_response',
        'publish_status',
        'attempt_count',
        'queued_at',
        'last_error',
        'publish_payload',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'published_at' => 'datetime',
        'queued_at' => 'datetime',
        'publish_response' => 'array',
        'publish_payload' => 'array',
    ];

    /**
     * Get the social asset that owns this schedule
     */
    public function asset()
    {
        return $this->belongsTo(SocialAsset::class, 'asset_id');
    }

    /**
     * Get the user who created this schedule
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Check if schedule is pending
     */
    public function isPending()
    {
        return $this->publish_status === 'pending';
    }

    /**
     * Check if schedule is published
     */
    public function isPublished()
    {
        return $this->publish_status === 'success';
    }

    /**
     * Check if schedule failed
     */
    public function isFailed()
    {
        return $this->publish_status === 'failed';
    }
}
