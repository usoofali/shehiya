<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'type',
        'published_by_user_id',
        'target_level',
        'state_id',
        'lga_id',
        'ward_id',
    ];

    public function publishedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by_user_id');
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    public function lga(): BelongsTo
    {
        return $this->belongsTo(Lga::class);
    }

    public function ward(): BelongsTo
    {
        return $this->belongsTo(Ward::class);
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->hasRole(['Super Administrator', 'National Administrator'])) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($user) {
            $q->where('target_level', 'national');

            if ($user->state_id) {
                $q->orWhere(function (Builder $sub) use ($user) {
                    $sub->where('target_level', 'state')->where('state_id', $user->state_id);
                });
            }

            if ($user->lga_id) {
                $q->orWhere(function (Builder $sub) use ($user) {
                    $sub->where('target_level', 'lga')->where('lga_id', $user->lga_id);
                });
            }

            if ($user->ward_id) {
                $q->orWhere(function (Builder $sub) use ($user) {
                    $sub->where('target_level', 'ward')->where('ward_id', $user->ward_id);
                });
            }
        });
    }
}
