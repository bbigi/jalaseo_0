<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GA4TrafficData extends Model
{
    protected $table = 'ane_ga4_traffic_data';

    protected $fillable = [
        'website_id',
        'connection_id',
        'date',
        'users',
        'new_users',
        'sessions',
        'page_views',
        'avg_session_duration',
        'bounce_rate',
        'engagement_rate',
        'conversions',
    ];

    protected $casts = [
        'date' => 'date',
        'avg_session_duration' => 'float',
        'bounce_rate' => 'float',
        'engagement_rate' => 'float',
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

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }
}
