<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebsiteAnalysis extends Model
{
    protected $table = 'ane_website_analyses';
    protected $fillable = ['website_id', 'type', 'status', 'result_json', 'started_at', 'finished_at'];
}
