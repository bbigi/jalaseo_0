<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GSCConnection extends Model
{
    protected $table = 'ane_gsc_connections';

    protected $fillable = [
        'website_id',
        'property_url',
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

    public function keywords()
    {
        return $this->hasMany(GSCKeyword::class, 'connection_id');
    }

    public function pages()
    {
        return $this->hasMany(GSCPage::class, 'connection_id');
    }

    public function syncJobs()
    {
        return $this->hasMany(GSCSyncJob::class, 'connection_id');
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
