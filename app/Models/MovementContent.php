<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MovementContent extends Model
{
    protected $fillable = [
        'key',
        'title',
        'body',
        'image_url',
    ];
}
