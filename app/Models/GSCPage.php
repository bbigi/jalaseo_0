<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GSCPage extends Model
{
    protected $table = 'ane_gsc_pages';

    protected $fillable = [
        'website_id',
        'connection_id',
        'sync_job_id',
        'date',
        'page_url',
        'clicks',
        'impressions',
        'ctr',
        'position',
    ];

    protected $casts = [
        'date' => 'date',
        'ctr' => 'float',
        'position' => 'float',
    ];

    // Relationships
    public function website()
    {
        return $this->belongsTo(Website::class, 'website_id');
    }

    public function connection()
    {
        return $this->belongsTo(GSCConnection::class, 'connection_id');
    }

    public function syncJob()
    {
        return $this->belongsTo(GSCSyncJob::class, 'sync_job_id');
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
}
