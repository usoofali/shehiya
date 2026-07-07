<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PollingUnit extends Model
{
    use HasFactory;

    protected $fillable = ['ward_id', 'name', 'code'];

    public function ward(): BelongsTo
    {
        return $this->belongsTo(Ward::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(Member::class);
    }
}
