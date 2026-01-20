<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GA4Connection extends Model
{
    protected $table = 'ane_ga4_connections';

    protected $fillable = [
        'website_id',
        'property_id',
        'account_id',
        'property_name',
        'access_token',
        'refresh_token',
        'token_expires_at',
        'verified',
        'last_sync_at',
        'sync_status',
        'sync_error',
    ];

    protected $casts = [
        'access_token' => 'encrypted',
        'refresh_token' => 'encrypted',
        'token_expires_at' => 'datetime',
        'last_sync_at' => 'datetime',
        'verified' => 'boolean',
    ];

    // Relationships
    public function website()
    {
        return $this->belongsTo(Website::class, 'website_id');
    }

    public function trafficData()
    {
        return $this->hasMany(GA4TrafficData::class, 'connection_id');
    }

    public function referralSources()
    {
        return $this->hasMany(GA4ReferralSource::class, 'connection_id');
    }

    public function pageStats()
    {
        return $this->hasMany(GA4PageStat::class, 'connection_id');
    }

    // Helpers
    public function isTokenExpired()
    {
        return $this->token_expires_at < now();
    }

    public function needsRefresh()
    {
        return $this->token_expires_at < now()->addMinutes(5);
    }
}
