<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KeywordTrace extends Model
{
    protected $table = 'ane_keyword_traces';
    protected $fillable = ['keyword_id', 'engine', 'position', 'url', 'traced_at'];
}
