<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    protected $fillable = ['name'];

    public function escoOfficials()
    {
        return $this->hasMany(EscoOfficial::class);
    }
}
