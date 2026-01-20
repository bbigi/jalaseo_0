<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocialSearch extends Model
{
    protected $table = 'ane_social_searches';

    protected $fillable = [
        'created_by',
        'keyword',
        'platform',
        'country_code',
        'language',
        'total_results',
        'status',
    ];

    public function results()
    {
        return $this->hasMany(SocialResult::class, 'search_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
