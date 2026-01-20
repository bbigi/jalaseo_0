<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ActivityLog extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ane_activity_logs';

    protected $fillable = ['user_id','action','entity_type','entity_id','description','ip_address'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
