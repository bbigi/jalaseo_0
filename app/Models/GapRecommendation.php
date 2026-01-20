<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GapRecommendation extends Model
{
    use HasFactory;

    protected $table = 'ane_gap_recommendations';

    protected $fillable = [
        'website_id',
        'competitor_id',
        'locale',
        'filter',
        'search',
        'content',
        'provider',
        'model',
        'tokens_used',
    ];
}
