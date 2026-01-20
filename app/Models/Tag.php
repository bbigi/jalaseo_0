<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;

    protected $table = 'ane_tags';

    protected $fillable = [
        'website_id',
        'wp_tag_id',
        'name',
        'slug',
    ];

    // Relationships
    public function website()
    {
        return $this->belongsTo(Website::class, 'website_id');
    }
}
