<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patron extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'title',
        'category',
        'photo_path',
        'order_index',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order_index' => 'integer',
    ];

    /**
     * Scope a query to only include active patrons, ordered by category hierarchy and order_index.
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true)
            ->orderByRaw("FIELD(category, 'Grand Patron', 'Patron', 'Royal Father')")
            ->orderBy('order_index')
            ->latest();
    }
}
