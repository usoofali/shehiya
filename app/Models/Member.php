<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Member extends Model
{
    use HasFactory;

    protected $fillable = [
        'membership_number',
        'first_name',
        'last_name',
        'gender',
        'dob',
        'phone',
        'email',
        'occupation',
        'photo_path',
        'state_id',
        'lga_id',
        'ward_id',
        'status',
        'registered_at',
    ];

    protected function casts(): array
    {
        return [
            'dob' => 'date',
            'registered_at' => 'datetime',
        ];
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

    public function verifications(): HasMany
    {
        return $this->hasMany(MemberVerification::class);
    }

    public function escoOfficial(): HasMany
    {
        return $this->hasMany(EscoOfficial::class);
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

        return $query->whereRaw('1 = 0');
    }
}
