<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GA4PageStat extends Model
{
    protected $table = 'ane_ga4_page_stats';

    protected $fillable = [
        'website_id',
        'connection_id',
        'date',
        'page_path',
        'page_title',
        'page_views',
        'unique_page_views',
        'avg_time_on_page',
        'bounce_rate',
        'exit_rate',
    ];

    protected $casts = [
        'date' => 'date',
        'avg_time_on_page' => 'float',
        'bounce_rate' => 'float',
        'exit_rate' => 'float',
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

    public function scopeTopPages($query, $limit = 10)
    {
        return $query->orderBy('page_views', 'desc')->limit($limit);
    }
}
