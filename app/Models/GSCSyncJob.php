<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GSCSyncJob extends Model
{
    protected $table = 'ane_gsc_sync_jobs';

    protected $fillable = [
        'connection_id',
        'start_date',
        'end_date',
        'job_status',
        'progress',
        'current_step',
        'keywords_synced',
        'pages_synced',
        'error_message',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // Relationships
    public function connection()
    {
        return $this->belongsTo(GSCConnection::class, 'connection_id');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('job_status', 'pending');
    }

    public function scopeProcessing($query)
    {
        return $query->where('job_status', 'processing');
    }

    public function scopeCompleted($query)
    {
        return $query->where('job_status', 'completed');
    }

    public function scopeFailed($query)
    {
        return $query->where('job_status', 'failed');
    }

    // Helpers
    public function markAsProcessing()
    {
        $this->update([
            'job_status' => 'processing',
            'started_at' => now(),
        ]);

        // Update connection status
        GSCConnection::where('id', $this->connection_id)->update([
            'sync_status' => 'syncing',
            'sync_error' => null,
        ]);
    }

    public function markAsCompleted()
    {
        $this->update([
            'job_status' => 'completed',
            'progress' => 100,
            'completed_at' => now(),
        ]);

        // Update connection status
        GSCConnection::where('id', $this->connection_id)->update([
            'sync_status' => 'completed',
            'last_sync_at' => now(),
            'sync_error' => null,
        ]);
    }

    public function markAsFailed($errorMessage)
    {
        $this->update([
            'job_status' => 'failed',
            'error_message' => $errorMessage,
            'completed_at' => now(),
        ]);

        // Update connection status
        GSCConnection::where('id', $this->connection_id)->update([
            'sync_status' => 'failed',
            'sync_error' => $errorMessage,
        ]);
    }
}
