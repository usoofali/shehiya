<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EscoOfficial extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'user_id',
        'full_name',
        'position_id',
        'phone',
        'email',
        'photo_path',
        'state_id',
        'lga_id',
        'ward_id',
        'polling_unit_id',
        'appointed_at',
        'status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'appointed_at' => 'date',
        ];
    }

    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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

    public function pollingUnit(): BelongsTo
    {
        return $this->belongsTo(PollingUnit::class);
    }

    public function scopeAccessibleBy(Builder $query, User $user): Builder
    {
        if ($user->hasRole(['Super Administrator', 'National Administrator'])) {
            return $query;
        }

        if ($user->hasRole('State Coordinator') && $user->state_id) {
            return $query->where('state_id', $user->state_id);
        }

        if ($user->hasRole('LGA Coordinator') && $user->lga_id) {
            return $query->where('lga_id', $user->lga_id);
        }

        if ($user->hasRole('Ward Coordinator') && $user->ward_id) {
            return $query->where('ward_id', $user->ward_id);
        }

        if ($user->hasRole('Polling Unit Coordinator') && $user->polling_unit_id) {
            return $query->where('polling_unit_id', $user->polling_unit_id);
        }

        return $query->whereRaw('1 = 0');
    }
}
