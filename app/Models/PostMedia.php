<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PostMedia extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ane_post_media';

    protected $fillable = ['post_id','media_id','role'];
}
