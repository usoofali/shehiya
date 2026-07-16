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
        'voter_card_path',
        'state_id',
        'lga_id',
        'ward_id',
        'polling_unit_id',
        'status',
        'registered_at',
        'referred_by_id',
    ];

    protected $appends = [
        'referrals_count',
        'verified_referrals_count',
        'referral_badge',
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

    public function pollingUnit(): BelongsTo
    {
        return $this->belongsTo(PollingUnit::class);
    }

    public function verifications(): HasMany
    {
        return $this->hasMany(MemberVerification::class);
    }

    public function escoOfficial(): HasMany
    {
        return $this->hasMany(EscoOfficial::class);
    }

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'referred_by_id');
    }

    public function referrals(): HasMany
    {
        return $this->hasMany(Member::class, 'referred_by_id');
    }

    public function getReferralsCountAttribute(): int
    {
        if (array_key_exists('referrals_count', $this->attributes)) {
            return (int) $this->attributes['referrals_count'];
        }

        return $this->referrals()->count();
    }

    public function getVerifiedReferralsCountAttribute(): int
    {
        if (array_key_exists('verified_referrals_count', $this->attributes)) {
            return (int) $this->attributes['verified_referrals_count'];
        }

        return $this->referrals()->where('status', 'verified')->count();
    }

    public function getReferralBadgeAttribute(): string
    {
        $count = $this->getVerifiedReferralsCountAttribute();

        if ($count >= 100000) {
            return 'Platinum Grandmaster';
        } elseif ($count >= 10000) {
            return 'Gold Master';
        } elseif ($count >= 1000) {
            return 'Silver Veteran';
        } elseif ($count >= 100) {
            return 'Bronze Ambassador';
        }

        return 'None';
    }

    public function scopeAccessibleBy(Builder $query, User $user): Builder
    {
        if ($user->hasRole(['Super Administrator', 'National Administrator'])) {
            return $query;
        }

        if ($user->hasRole('State Coordinator') && $user->state_id) {
            return $query->where('members.state_id', $user->state_id);
        }

        if ($user->hasRole('LGA Coordinator') && $user->lga_id) {
            return $query->where('members.lga_id', $user->lga_id);
        }

        if ($user->hasRole('Ward Coordinator') && $user->ward_id) {
            return $query->where('members.ward_id', $user->ward_id);
        }

        if ($user->hasRole('Polling Unit Coordinator') && $user->polling_unit_id) {
            return $query->where('members.polling_unit_id', $user->polling_unit_id);
        }

        return $query->whereRaw('1 = 0');
    }
}
