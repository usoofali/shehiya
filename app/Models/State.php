<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class State extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'code'];

    public function lgas(): HasMany
    {
        return $this->hasMany(Lga::class);
    }

    public function wards(): HasManyThrough
    {
        return $this->hasManyThrough(Ward::class, Lga::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(Member::class);
    }
}
