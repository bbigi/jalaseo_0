<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GA4ReferralSource extends Model
{
    protected $table = 'ane_ga4_referral_sources';

    protected $fillable = [
        'website_id',
        'connection_id',
        'date',
        'source',
        'medium',
        'users',
        'sessions',
        'page_views',
        'avg_session_duration',
        'bounce_rate',
    ];

    protected $casts = [
        'date' => 'date',
        'avg_session_duration' => 'float',
        'bounce_rate' => 'float',
    ];

    // Relationships
    public function website()
    {
        return $this->belongsTo(Website::class, 'website_id');
    }

    public function connection()
    {
        return $this->belongsTo(GA4Connection::class, 'connection_id');
    }

    // Scopes
    public function scopeForWebsite($query, $websiteId)
    {
        return $query->where('website_id', $websiteId);
    }

    public function scopeLastDays($query, $days = 30)
    {
        return $query->where('date', '>=', now()->subDays($days));
    }

    public function scopeByMedium($query, $medium)
    {
        return $query->where('medium', $medium);
    }

    public function scopeReferralOnly($query)
    {
        return $query->where('medium', 'referral');
    }
}
